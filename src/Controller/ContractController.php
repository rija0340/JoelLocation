<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Event\ContractSignedByClientEvent;
use App\Event\ContractSignatureStartedEvent;
use App\Event\ContractSignatureCompletedEvent;
use App\Entity\ContractSignature;
use App\Repository\ContractRepository;
use App\Service\ContractService;
use App\Service\SignatureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\EmailManagerService;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contract")
 */
class ContractController extends AbstractController
{
    public $contractService;
    public $signatureService;
    public $contractRepository;
    public $entityManager;
    public $emailManagerService;
    public $eventDispatcher;

    public function __construct(
        ContractService $contractService,
        SignatureService $signatureService,
        ContractRepository $contractRepository,
        EntityManagerInterface $entityManager,
        EmailManagerService $emailManagerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->contractService = $contractService;
        $this->signatureService = $signatureService;
        $this->contractRepository = $contractRepository;
        $this->entityManager = $entityManager;
        $this->emailManagerService = $emailManagerService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/sign/reservation/{id}/{hash}", name="contract_sign_client_reservation_hash", methods={"GET"}, defaults={"hash"=null})
     * @Route("/sign/reservation/{id}", name="contract_sign_client_reservation", methods={"GET"})
     */
    public function signClientByReservation(int $id, string $hash = null): Response
    {
        $reservation = $this->entityManager->getRepository(\App\Entity\Reservation::class)->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation introuvable');
        }

        // Check if the current user is authenticated and is the client of this reservation
        // OR if a valid hash is provided
        $isValidHash = ($hash && sha1($reservation->getId()) === $hash);

        if (!$isValidHash && !$this->isGranted('ROLE_CLIENT')) {
            // Redirect to login if not authenticated and no valid hash
            return $this->redirectToRoute('app_login');
        }

        if (!$isValidHash) {
            $user = $this->getUser();
            if ($user !== $reservation->getClient()) {
                // If the logged-in user is not the client of this reservation, deny access
                throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à signer ce contrat.');
            }
        }

        // Get or create the contract automatically
        $contract = $this->contractService->getOrCreateContract($reservation);

        // Redirect to the actual contract signing page
        if ($isValidHash) {
            return $this->redirectToRoute('contract_sign_client_hash', ['id' => $contract->getId(), 'hash' => $hash]);
        }
        return $this->redirectToRoute('contract_sign_client', ['id' => $contract->getId()]);
    }

    /**
     * @Route("/admin/sign/reservation/{id}", name="contract_sign_admin_reservation", methods={"GET"})
     */
    public function signAdminByReservation(int $id): Response
    {
        $reservation = $this->entityManager->getRepository(\App\Entity\Reservation::class)->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation introuvable');
        }

        // Get or create the contract automatically
        $contract = $this->contractService->getOrCreateContract($reservation);

        // Redirect to the actual contract signing page
        return $this->redirectToRoute('contract_sign_admin', ['id' => $contract->getId()]);
    }

    /**
     * @Route("/espaceclient/sign/{id}/{hash}", name="contract_sign_client_hash", methods={"GET"}, defaults={"hash"=null})
     * @Route("/espaceclient/sign/{id}", name="contract_sign_client", methods={"GET"})
     */
    public function signClient(int $id, string $hash = null): Response
    {
        $contract = $this->contractRepository->find($id);

        if (!$contract) {
            throw $this->createNotFoundException('Contrat introuvable');
        }

        $isValidHash = ($hash && sha1($contract->getReservation()->getId()) === $hash);

        if (!$isValidHash && !$this->isGranted('ROLE_CLIENT')) {
            return $this->redirectToRoute('app_login');
        }

        if (!$isValidHash) {
            $user = $this->getUser();
            if ($user !== $contract->getReservation()->getClient()) {
                throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à signer ce contrat.');
            }
        }

        $isPaid = $this->contractService->isReservationPaid($contract->getReservation());

        return $this->render('contract/sign_client.html.twig', [
            'contract' => $contract,
            'is_paid' => $isPaid,
            'hash' => $hash
        ]);
    }

    /**
     * @Route("/espaceclient/sign/{id}/process/{hash}", name="contract_sign_client_process_hash", methods={"POST"}, defaults={"hash"=null})
     * @Route("/espaceclient/sign/{id}/process", name="contract_sign_client_process", methods={"POST"})
     */
    public function processClientSignature(Request $request, int $id, string $hash = null): Response
    {
        $contract = $this->contractRepository->find($id);
        if (!$contract) {
            throw $this->createNotFoundException('Contrat introuvable');
        }

        // Check if the current user is authenticated and is the client of this reservation
        if (!$this->isGranted('ROLE_CLIENT')) {
            // Redirect to login if not authenticated
            return $this->redirectToRoute('app_login');
        }
        if (!$isValidHash) {
            $user = $this->getUser();
            if ($user !== $contract->getReservation()->getClient()) {
                throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à signer ce contrat.');
            }
        }

        $signatureImage = $request->request->get('signature_data');
        if (empty($signatureImage)) {
            $this->addFlash('error', 'La signature est requise.');
            if ($isValidHash) {
                return $this->redirectToRoute('contract_sign_client_hash', ['id' => $id, 'hash' => $hash]);
            }
            return $this->redirectToRoute('contract_sign_client', ['id' => $id]);
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
                $signatureImage
            );

            // Dispatch signature completed event
            $completeEvent = new ContractSignatureCompletedEvent(
                $contract,
                $signature,
                ContractSignature::TYPE_CLIENT
            );
            $this->eventDispatcher->dispatch($completeEvent, ContractSignatureCompletedEvent::NAME);

            $this->addFlash('success', 'Contrat signé avec succès !');

            // Dispatch event to notify admin that the client has signed
            $baseUrl = $this->generateUrl('reservation_show', ['id' => $contract->getReservation()->getId()], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
            $event = new ContractSignedByClientEvent($contract->getReservation(), $baseUrl);
            $this->eventDispatcher->dispatch($event, ContractSignedByClientEvent::NAME);

            if ($isValidHash) {
                return $this->redirectToRoute('client_reservation_signature_hash', ['hashedId' => $hash]);
            }
            return $this->redirectToRoute('client_reservation_show', ['id' => $contract->getReservation()->getId()]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la signature : ' . $e->getMessage());
            if ($isValidHash) {
                return $this->redirectToRoute('contract_sign_client_hash', ['id' => $id, 'hash' => $hash]);
            }
            return $this->redirectToRoute('contract_sign_client', ['id' => $id]);
        }
    }

    /**
     * @Route("/admin/sign/{id}", name="contract_sign_admin", methods={"GET"})
     */
    public function signAdmin(int $id): Response
    {
        $contract = $this->contractRepository->find($id);

        if (!$contract) {
            throw $this->createNotFoundException('Contrat introuvable');
        }

        return $this->render('contract/sign_admin.html.twig', [
            'contract' => $contract,
        ]);
    }

    /**
     * @Route("/admin/sign/{id}/process", name="contract_sign_admin_process", methods={"POST"})
     */
    public function processAdminSignature(Request $request, int $id): Response
    {
        $contract = $this->contractRepository->find($id);
        if (!$contract) {
            throw $this->createNotFoundException('Contrat introuvable');
        }

        $signatureImage = $request->request->get('signature_data');
        if (empty($signatureImage)) {
            $this->addFlash('error', 'La signature est requise.');
            return $this->redirectToRoute('contract_sign_admin', ['id' => $id]);
        }

        try {
            // Dispatch signature started event
            $startEvent = new ContractSignatureStartedEvent(
                $contract,
                ContractSignature::TYPE_ADMIN,
                $request->getClientIp(),
                $request->headers->get('User-Agent')
            );
            $this->eventDispatcher->dispatch($startEvent, ContractSignatureStartedEvent::NAME);

            // Generate admin keypair (simulated for this flow)
            $keypair = $this->signatureService->generateKeypair();

            $cryptoSignature = $this->signatureService->createSignature(
                $contract->getContractHash(),
                $keypair['private_key']
            );

            $signature = $this->contractService->processAdminSignature(
                $contract,
                $cryptoSignature,
                $keypair['public_key'],
                $request->getClientIp(),
                $request->headers->get('User-Agent'),
                $signatureImage // The visual signature image
            );

            // Dispatch signature completed event
            $completeEvent = new ContractSignatureCompletedEvent(
                ContractSignature::TYPE_ADMIN
            );
            $this->eventDispatcher->dispatch($completeEvent, ContractSignatureCompletedEvent::NAME);

            $this->addFlash('success', 'Contrat validé par l\'administrateur !');
            // Redirect to reservation details with anchor to signature section
            return $this->redirectToRoute('reservation_show', [
            ]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la validation : ' . $e->getMessage());
            return $this->redirectToRoute('contract_sign_admin', ['id' => $id]);
        }
    }
}