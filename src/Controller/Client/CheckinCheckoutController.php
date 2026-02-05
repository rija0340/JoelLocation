<?php

namespace App\Controller\Client;

use App\Entity\ContractSignature;
use App\Repository\ContractRepository;
use App\Repository\ReservationRepository;
use App\Service\ContractService;
use App\Service\SignatureService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Event\ContractSignatureStartedEvent;
use App\Event\ContractSignatureCompletedEvent;
use App\Event\CheckoutSignedByClientEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class CheckinCheckoutController extends AbstractController
{
    private $contractRepository;
    private $reservationRepository;
    private $contractService;
    private $signatureService;
    private $eventDispatcher;

    public function __construct(
        ContractRepository $contractRepository,
        ReservationRepository $reservationRepository,
        ContractService $contractService,
        SignatureService $signatureService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->contractRepository = $contractRepository;
        $this->reservationRepository = $reservationRepository;
        $this->contractService = $contractService;
        $this->signatureService = $signatureService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/signature-etat-lieux-depart/{hashedId}", name="client_checkin_sign_hash", methods={"GET"})
     */
    public function signCheckinHash(string $hashedId): Response
    {
        $reservation = $this->reservationRepository->findByHashedId($hashedId);
        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée.');
        }

        $contract = $this->contractService->getOrCreateContract($reservation);

        // Rediriger vers la signature du contrat (qui est maintenant le départ)
        return $this->redirectToRoute('contract_sign_client_hash', [
            'id' => $contract->getId(),
            'hash' => $hashedId
        ]);
    }

    /**
     * @Route("/signature-etat-lieux-depart/{hashedId}/process", name="client_checkin_sign_process_hash", methods={"POST"})
     */
    public function processCheckinSignatureHash(Request $request, string $hashedId): Response
    {
        $reservation = $this->reservationRepository->findByHashedId($hashedId);
        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée.');
        }

        $contract = $this->contractService->getOrCreateContract($reservation);

        // Rediriger vers le traitement du contrat
        return $this->redirectToRoute('contract_sign_client_process_hash', [
            'id' => $contract->getId(),
            'hash' => $hashedId
        ]);
    }

    /**
     * @Route("/espaceclient/signature-etat-lieux-retour/{id}", name="client_checkout_sign", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function signCheckout(int $id): Response
    {
        $reservation = $this->reservationRepository->find($id);
        if (!$reservation || $reservation->getClient() !== $this->getUser()) {
            throw $this->createNotFoundException('Réservation non trouvée.');
        }

        $contract = $this->contractService->getOrCreateContract($reservation);

        // Vérifier que le checkin est signé avant de permettre le checkout
        if (!$contract->canSignCheckout()) {
            $this->addFlash('warning', 'La signature de départ (contrat) doit être effectuée avant le retour.');
            return $this->redirectToRoute('client_reservation_show', ['id' => $id]);
        }

        return $this->render('vehicle_check/checkout_sign_client.html.twig', [
            'contract' => $contract,
            'reservation' => $reservation,
            'document_type' => ContractSignature::DOC_CHECKOUT,
            'is_signed_by_client' => $contract->isSignedByClient(ContractSignature::DOC_CHECKOUT),
            'is_signed_by_admin' => $contract->isSignedByAdmin(ContractSignature::DOC_CHECKOUT),
            'is_fully_signed' => $contract->isFullySigned(ContractSignature::DOC_CHECKOUT),
            'checkin_signed' => true,
            'is_client_view' => true,
        ]);
    }

    /**
     * @Route("/signature-etat-lieux-retour/{hashedId}", name="client_checkout_sign_hash", methods={"GET"})
     */
    public function signCheckoutHash(string $hashedId): Response
    {
        $reservation = $this->reservationRepository->findByHashedId($hashedId);
        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée.');
        }

        $contract = $this->contractService->getOrCreateContract($reservation);

        // Vérifier que le checkin est signé avant de permettre le checkout
        if (!$contract->canSignCheckout()) {
            $this->addFlash('warning', 'La signature de départ (contrat) doit être effectuée avant le retour.');
            return $this->redirectToRoute('client_reservation_signature_hash', ['hashedId' => $hashedId]);
        }

        return $this->render('vehicle_check/checkout_sign_client.html.twig', [
            'contract' => $contract,
            'reservation' => $reservation,
            'document_type' => ContractSignature::DOC_CHECKOUT,
            'is_signed_by_client' => $contract->isSignedByClient(ContractSignature::DOC_CHECKOUT),
            'is_signed_by_admin' => $contract->isSignedByAdmin(ContractSignature::DOC_CHECKOUT),
            'is_fully_signed' => $contract->isFullySigned(ContractSignature::DOC_CHECKOUT),
            'checkin_signed' => true,
            'is_client_view' => true,
            'hashedId' => $hashedId,
        ]);
    }

    /**
     * @Route("/espaceclient/signature-etat-lieux-retour/{id}/process", name="client_checkout_sign_process", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function processCheckoutSignature(Request $request, int $id): Response
    {
        $reservation = $this->reservationRepository->find($id);
        if (!$reservation || $reservation->getClient() !== $this->getUser()) {
            throw $this->createNotFoundException('Réservation non trouvée.');
        }

        $contract = $this->contractService->getOrCreateContract($reservation);

        // Vérifier que le checkin est signé
        if (!$contract->canSignCheckout()) {
            $this->addFlash('error', 'La signature de départ (contrat) doit être effectuée avant le retour.');
            return $this->redirectToRoute('client_reservation_show', ['id' => $id]);
        }

        $signatureImage = $request->request->get('signature_data');

        if (empty($signatureImage)) {
            $this->addFlash('error', 'La signature est requise.');
            return $this->redirectToRoute('client_checkout_sign', ['id' => $id]);
        }

        try {
            // Dispatch signature started event
            $startEvent = new ContractSignatureStartedEvent(
                $contract,
                ContractSignature::TYPE_CLIENT,
                $request->getClientIp(),
                $request->headers->get('User-Agent')
            );
            $this->eventDispatcher->dispatch($startEvent, ContractSignatureStartedEvent::NAME);

            // Generate keypair
            $keypair = $this->signatureService->generateKeypair();
            $cryptoSignature = $this->signatureService->createSignature(
                $contract->getContractHash(),
                $keypair['private_key']
            );

            $signature = $this->contractService->processClientSignature(
                $contract,
                $cryptoSignature,
                $keypair['public_key'],
                $request->getClientIp(),
                $request->headers->get('User-Agent'),
                false,
                $signatureImage,
                ContractSignature::DOC_CHECKOUT
            );

            // Dispatch signature completed event
            $completeEvent = new ContractSignatureCompletedEvent(
                $contract,
                $signature,
                ContractSignature::TYPE_CLIENT
            );
            $this->eventDispatcher->dispatch($completeEvent, ContractSignatureCompletedEvent::NAME);

            $this->addFlash('success', "Signature de l'état des lieux retour enregistrée !");

            // Dispatch event to notify admin that the client has signed the checkout
            $baseUrl = $this->generateUrl('reservation_show', ['id' => $reservation->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            $checkoutEvent = new CheckoutSignedByClientEvent($reservation, $baseUrl);
            $this->eventDispatcher->dispatch($checkoutEvent, CheckoutSignedByClientEvent::NAME);

            return $this->redirectToRoute('client_reservation_show', ['id' => $id]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la signature : ' . $e->getMessage());
            return $this->redirectToRoute('client_checkout_sign', ['id' => $id]);
        }
    }

    /**
     * @Route("/signature-etat-lieux-retour/{hashedId}/process", name="client_checkout_sign_process_hash", methods={"POST"})
     */
    public function processCheckoutSignatureHash(Request $request, string $hashedId): Response
    {
        $reservation = $this->reservationRepository->findByHashedId($hashedId);
        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée.');
        }

        $contract = $this->contractService->getOrCreateContract($reservation);

        // Vérifier que le checkin est signé
        if (!$contract->canSignCheckout()) {
            $this->addFlash('error', 'La signature de départ (contrat) doit être effectuée avant le retour.');
            return $this->redirectToRoute('client_reservation_signature_hash', ['hashedId' => $hashedId]);
        }

        $signatureImage = $request->request->get('signature_data');

        if (empty($signatureImage)) {
            $this->addFlash('error', 'La signature est requise.');
            return $this->redirectToRoute('client_checkout_sign_hash', ['hashedId' => $hashedId]);
        }

        try {
            // Dispatch signature started event
            $startEvent = new ContractSignatureStartedEvent(
                $contract,
                ContractSignature::TYPE_CLIENT,
                $request->getClientIp(),
                $request->headers->get('User-Agent')
            );
            $this->eventDispatcher->dispatch($startEvent, ContractSignatureStartedEvent::NAME);

            // Generate keypair
            $keypair = $this->signatureService->generateKeypair();
            $cryptoSignature = $this->signatureService->createSignature(
                $contract->getContractHash(),
                $keypair['private_key']
            );

            $signature = $this->contractService->processClientSignature(
                $contract,
                $cryptoSignature,
                $keypair['public_key'],
                $request->getClientIp(),
                $request->headers->get('User-Agent'),
                false,
                $signatureImage,
                ContractSignature::DOC_CHECKOUT
            );

            // Dispatch signature completed event
            $completeEvent = new ContractSignatureCompletedEvent(
                $contract,
                $signature,
                ContractSignature::TYPE_CLIENT
            );
            $this->eventDispatcher->dispatch($completeEvent, ContractSignatureCompletedEvent::NAME);

            $this->addFlash('success', "Signature de l'état des lieux retour enregistrée !");

            // Dispatch event to notify admin that the client has signed the checkout
            $baseUrl = $this->generateUrl('reservation_show', ['id' => $reservation->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            $checkoutEvent = new CheckoutSignedByClientEvent($reservation, $baseUrl);
            $this->eventDispatcher->dispatch($checkoutEvent, CheckoutSignedByClientEvent::NAME);

            return $this->redirectToRoute('client_checkout_sign_hash', ['hashedId' => $hashedId]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la signature : ' . $e->getMessage());
            return $this->redirectToRoute('client_checkout_sign_hash', ['hashedId' => $hashedId]);
        }
    }

}
