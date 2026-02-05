<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Entity\ContractSignature;
use App\Event\ContractSignatureStartedEvent;
use App\Event\ContractSignatureCompletedEvent;
use App\Repository\ContractRepository;
use App\Service\ContractService;
use App\Service\SignatureService;
use App\Service\EmailManagerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur pour la signature des états des lieux (départ et retour)
 * @Route("/vehicle-check")
 */
class CheckinCheckoutController extends AbstractController
{
    private $contractService;
    private $signatureService;
    private $emailManagerService;
    private $contractRepository;
    private $entityManager;
    private $eventDispatcher;

    public function __construct(
        ContractService $contractService,
        SignatureService $signatureService,
        EmailManagerService $emailManagerService,
        ContractRepository $contractRepository,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->contractService = $contractService;
        $this->signatureService = $signatureService;
        $this->emailManagerService = $emailManagerService;
        $this->contractRepository = $contractRepository;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }



    /**
     * @Route("/checkout/sign/{id}", name="vehicle_checkout_sign", methods={"GET"})
     * @Route("/checkout/sign-admin/{id}", name="vehicle_checkout_sign_admin", methods={"GET"}, defaults={"signer_type"="admin"})
     * @Route("/checkout/sign-client-backoffice/{id}", name="vehicle_checkout_sign_client_backoffice", methods={"GET"}, defaults={"signer_type"="client"})
     */
    public function signCheckout(int $id, Request $request): Response
    {
        $contract = $this->contractRepository->find($id);
        if (!$contract) {
            throw $this->createNotFoundException('Contrat introuvable');
        }

        // Vérifier que le checkin est signé avant de permettre le checkout
        if (!$contract->canSignCheckout()) {
            $this->addFlash('warning', 'La signature de départ (contrat) doit être effectuée avant le retour.');
            return $this->redirectToRoute('reservation_show', ['id' => $contract->getReservation()->getId()]);
        }

        $reservation = $contract->getReservation();
        $signerType = $request->attributes->get('signer_type');

        if (!$signerType) {
            return $this->redirectToRoute('vehicle_check_summary', ['id' => $id]);
        }

        $template = 'vehicle_check/checkout_sign_admin.html.twig';
        if ($signerType === 'client') {
            $template = 'vehicle_check/checkout_sign_client_backoffice.html.twig';
        }

        return $this->render($template, [
            'contract' => $contract,
            'reservation' => $reservation,
            'document_type' => ContractSignature::DOC_CHECKOUT,
            'is_signed_by_client' => $contract->isSignedByClient(ContractSignature::DOC_CHECKOUT),
            'is_signed_by_admin' => $contract->isSignedByAdmin(ContractSignature::DOC_CHECKOUT),
            'is_fully_signed' => $contract->isFullySigned(ContractSignature::DOC_CHECKOUT),
            'checkin_signed' => true,
            'is_admin_view' => true,
            'signer_type' => $signerType,
        ]);
    }

    /**
     * Traite la signature de l'état des lieux retour (checkout)
     * 
     * @Route("/checkout/sign/{id}/process", name="vehicle_checkout_sign_process", methods={"POST"})
     */
    public function processCheckoutSignature(Request $request, int $id): Response
    {
        $contract = $this->contractRepository->find($id);
        if (!$contract) {
            throw $this->createNotFoundException('Contrat introuvable');
        }

        // Vérifier que le checkin est signé
        if (!$contract->canSignCheckout()) {
            $this->addFlash('error', 'La signature de départ (contrat) doit être effectuée avant le retour.');
            return $this->redirectToRoute('reservation_show', ['id' => $contract->getReservation()->getId()]);
        }

        $signatureImage = $request->request->get('signature_data');
        $signatureType = $request->request->get('signature_type'); // 'client' ou 'admin'

        if (empty($signatureImage)) {
            $this->addFlash('error', 'La signature est requise.');
            $route = $signatureType === 'client' ? 'vehicle_checkout_sign_client_backoffice' : 'vehicle_checkout_sign_admin';
            return $this->redirectToRoute($route, ['id' => $id]);
        }

        if (!in_array($signatureType, [ContractSignature::TYPE_CLIENT, ContractSignature::TYPE_ADMIN])) {
            $this->addFlash('error', 'Type de signature invalide.');
            return $this->redirectToRoute('vehicle_check_summary', ['id' => $id]);
        }

        // Si signature client en backoffice, vérifier la confirmation
        if ($signatureType === ContractSignature::TYPE_CLIENT && !$request->request->get('confirm_backoffice')) {
            $this->addFlash('error', 'Vous devez confirmer que le client signe en votre présence.');
            return $this->redirectToRoute('vehicle_checkout_sign_client_backoffice', ['id' => $id]);
        }

        try {
            // Dispatch signature started event
            $startEvent = new ContractSignatureStartedEvent(
                $contract,
                $signatureType,
                $request->getClientIp(),
                $request->headers->get('User-Agent') . ($this->getUser() ? ' [BACKOFFICE]' : '')
            );
            $this->eventDispatcher->dispatch($startEvent, ContractSignatureStartedEvent::NAME);

            // Generate keypair
            $keypair = $this->signatureService->generateKeypair();
            $cryptoSignature = $this->signatureService->createSignature(
                $contract->getContractHash(),
                $keypair['private_key']
            );

            // Process signature based on type
            if ($signatureType === ContractSignature::TYPE_CLIENT) {
                $signature = $this->contractService->processClientSignature(
                    $contract,
                    $cryptoSignature,
                    $keypair['public_key'],
                    $request->getClientIp() . ($this->getUser() ? ' [BACKOFFICE]' : ''),
                    $request->headers->get('User-Agent') . ($this->getUser() ? ' [BACKOFFICE]' : ''),
                    false,
                    $signatureImage,
                    ContractSignature::DOC_CHECKOUT
                );
            } else {
                $signature = $this->contractService->processAdminSignature(
                    $contract,
                    $cryptoSignature,
                    $keypair['public_key'],
                    $request->getClientIp(),
                    $request->headers->get('User-Agent'),
                    $signatureImage,
                    ContractSignature::DOC_CHECKOUT
                );
            }

            // Dispatch signature completed event
            $completeEvent = new ContractSignatureCompletedEvent(
                $contract,
                $signature,
                $signatureType
            );
            $this->eventDispatcher->dispatch($completeEvent, ContractSignatureCompletedEvent::NAME);

            $signerLabel = $signatureType === ContractSignature::TYPE_CLIENT ? 'Client' : 'Administrateur';
            $this->addFlash('success', "Signature de l'état des lieux retour enregistrée ({$signerLabel}) !");

            // Redirection vers la réservation
            return $this->redirectToRoute('reservation_show', [
                'id' => $contract->getReservation()->getId()
            ]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la signature : ' . $e->getMessage());
            return $this->redirectToRoute('vehicle_checkout_sign', ['id' => $id]);
        }
    }




    /**
     * @Route("/checkout/send-email/{id}", name="vehicle_checkout_send_email", methods={"GET"})
     */
    public function sendCheckoutEmail(Request $request, int $id): Response
    {
        $contract = $this->contractRepository->find($id);
        if (!$contract) {
            throw $this->createNotFoundException('Contrat introuvable');
        }

        if (!$contract->canSignCheckout()) {
            $this->addFlash('error', 'L\'état des lieux de départ doit être signé avant de pouvoir demander la signature du retour.');
            return $this->redirectToRoute('reservation_show', ['id' => $contract->getReservation()->getId()]);
        }

        $this->emailManagerService->sendCheckoutSignatureRequest($request, $contract->getReservation());
        $this->addFlash('success', 'Email de demande de signature (Retour) envoyé avec succès.');
        return $this->redirectToRoute('reservation_show', ['id' => $contract->getReservation()->getId()]);
    }
}
