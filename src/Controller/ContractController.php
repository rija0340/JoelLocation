<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Repository\ContractRepository;
use App\Service\ContractService;
use App\Service\SignatureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    public function __construct(
        ContractService $contractService,
        SignatureService $signatureService,
        ContractRepository $contractRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->contractService = $contractService;
        $this->signatureService = $signatureService;
        $this->contractRepository = $contractRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/sign/reservation/{id}", name="contract_sign_client_reservation", methods={"GET"})
     */
    public function signClientByReservation(int $id): Response
    {
        $reservation = $this->entityManager->getRepository(\App\Entity\Reservation::class)->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation introuvable');
        }

        // Check if the current user is authenticated and is the client of this reservation
        if (!$this->isGranted('ROLE_CLIENT')) {
            // Redirect to login if not authenticated
            return $this->redirectToRoute('app_login');
        }

        $user = $this->getUser();
        if ($user !== $reservation->getClient()) {
            // If the logged-in user is not the client of this reservation, deny access
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à signer ce contrat.');
        }

        // Get or create the contract automatically
        $contract = $this->contractService->getOrCreateContract($reservation);

        // Redirect to the actual contract signing page
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
     * @Route("/espaceclient/sign/{id}", name="contract_sign_client", methods={"GET"})
     */
    public function signClient(int $id): Response
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

        $user = $this->getUser();
        if ($user !== $contract->getReservation()->getClient()) {
            // If the logged-in user is not the client of this reservation, deny access
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à signer ce contrat.');
        }

        $isPaid = $this->contractService->isReservationPaid($contract->getReservation());

        return $this->render('contract/sign_client.html.twig', [
            'contract' => $contract,
            'is_paid' => $isPaid,
        ]);
    }

    /**
     * @Route("/espaceclient/sign/{id}/process", name="contract_sign_client_process", methods={"POST"})
     */
    public function processClientSignature(Request $request, int $id): Response
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

        $user = $this->getUser();
        if ($user !== $contract->getReservation()->getClient()) {
            // If the logged-in user is not the client of this reservation, deny access
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à signer ce contrat.');
        }

        $signatureImage = $request->request->get('signature_data');
        if (empty($signatureImage)) {
            $this->addFlash('error', 'La signature est requise.');
            return $this->redirectToRoute('contract_sign_client', ['id' => $id]);
        }

        try {
            // Generate a temporary keypair for the client session
            // In a real scenario, this might come from a user certificate or be generated per session
            $keypair = $this->signatureService->generateKeypair();

            // Create the cryptographic signature of the contract hash
            $cryptoSignature = $this->signatureService->createSignature(
                $contract->getContractHash(),
                $keypair['private_key']
            );

            // Process the signature
            // Note: We store the visual signature (base64 image) in the 'signatureData' field for now,
            // but ideally we should separate visual representation from cryptographic data.
            // For this implementation, we'll store the crypto signature as the main data,
            // and we might want to store the image separately or encoded.
            // Let's store the crypto signature for security, but we acknowledge the visual one was provided.

            $this->contractService->processClientSignature(
                $contract,
                $cryptoSignature, // The cryptographic signature
                $keypair['public_key'],
                $request->getClientIp(),
                $request->headers->get('User-Agent'),
                false, // skipPaymentCheck
                $signatureImage // The visual signature image
            );

            $this->addFlash('success', 'Contrat signé avec succès !');
            return $this->redirectToRoute('client_reservation_show', ['id' => $contract->getReservation()->getId()]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la signature : ' . $e->getMessage());
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
            // Generate admin keypair (simulated for this flow)
            $keypair = $this->signatureService->generateKeypair();

            $cryptoSignature = $this->signatureService->createSignature(
                $contract->getContractHash(),
                $keypair['private_key']
            );

            $this->contractService->processAdminSignature(
                $contract,
                $cryptoSignature,
                $keypair['public_key'],
                $request->getClientIp(),
                $request->headers->get('User-Agent'),
                $signatureImage // The visual signature image
            );

            $this->addFlash('success', 'Contrat validé par l\'administrateur !');
            // Redirect to reservation details with anchor to signature section
            return $this->redirectToRoute('reservation_show', [
                'id' => $contract->getReservation()->getId()
            ]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la validation : ' . $e->getMessage());
            return $this->redirectToRoute('contract_sign_admin', ['id' => $id]);
        }
    }
}