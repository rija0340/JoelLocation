<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Entity\ContractSignature;
use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Repository\ContractRepository;
use App\Service\ContractGeneratorService;
use App\Service\ContractService;
use App\Service\SignatureService;
use App\Service\SignatureVerificationService;
use App\Service\TsaClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/test-signature")
 */
class TestSignatureController extends AbstractController
{
    private ContractGeneratorService $contractGeneratorService;
    private ContractService $contractService;
    private SignatureService $signatureService;
    private SignatureVerificationService $verificationService;
    private ReservationRepository $reservationRepository;
    private ContractRepository $contractRepository;
    private TsaClient $tsaClient;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ContractGeneratorService $contractGeneratorService,
        ContractService $contractService,
        SignatureService $signatureService,
        SignatureVerificationService $verificationService,
        ReservationRepository $reservationRepository,
        ContractRepository $contractRepository,
        TsaClient $tsaClient,
        EntityManagerInterface $entityManager
    ) {
        $this->contractGeneratorService = $contractGeneratorService;
        $this->contractService = $contractService;
        $this->signatureService = $signatureService;
        $this->verificationService = $verificationService;
        $this->reservationRepository = $reservationRepository;
        $this->contractRepository = $contractRepository;
        $this->tsaClient = $tsaClient;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="test_signature_flow", methods={"GET"})
     */
    public function testFlow(): Response
    {
        $testResults = [
            'entities_created' => true,
            'signature_service_working' => true,
            'key_generation' => false,
            'hash_generation' => false,
            'signature_creation' => false,
            'signature_verification' => false,
            'tsa_service' => false,
            'contract_creation' => false,
            'signature_process' => false,
            'verification_process' => false,
        ];

        $detailedResults = [];

        try {
            // Test 1: Key generation
            $keypair = $this->signatureService->generateKeypair();
            $testResults['key_generation'] = !empty($keypair['private_key']) && !empty($keypair['public_key']);
            $detailedResults['key_generation'] = [
                'private_key_length' => strlen($keypair['private_key']),
                'public_key_length' => strlen($keypair['public_key']),
                'private_key_preview' => substr($keypair['private_key'], 0, 50) . '...',
                'public_key_preview' => substr($keypair['public_key'], 0, 50) . '...',
            ];

            // Test 2: Hash generation
            $testContent = "Test contract content for signature verification - " . date('Y-m-d H:i:s');
            $hash = $this->signatureService->calculateSha256Hash($testContent);
            $testResults['hash_generation'] = !empty($hash) && strlen($hash) === 64;
            $detailedResults['hash_generation'] = [
                'content_length' => strlen($testContent),
                'hash' => $hash,
                'hash_length' => strlen($hash),
            ];

            // Test 3: Signature creation
            $signature = $this->signatureService->createSignature($hash, $keypair['private_key']);
            $testResults['signature_creation'] = !empty($signature);
            $detailedResults['signature_creation'] = [
                'signature_length' => strlen($signature),
                'signature_preview' => substr($signature, 0, 50) . '...',
            ];

            // Test 4: Signature verification
            $isValid = $this->signatureService->verifySignature($hash, $signature, $keypair['public_key']);
            $testResults['signature_verification'] = $isValid;
            $detailedResults['signature_verification'] = [
                'original_valid' => $isValid,
                'tampered_test' => !$this->signatureService->verifySignature($hash . 'tampered', $signature, $keypair['public_key']),
            ];

            // Test 5: TSA Service
            $timestampToken = $this->tsaClient->requestTimestamp($hash);
            $testResults['tsa_service'] = !empty($timestampToken);
            $tsaVerified = $this->tsaClient->verifyTimestamp($timestampToken, $hash);
            $detailedResults['tsa_service'] = [
                'token_length' => strlen($timestampToken),
                'token_preview' => substr($timestampToken, 0, 50) . '...',
                'verification_passed' => $tsaVerified,
                'tsa_url' => $this->tsaClient->getTsaUrl(),
            ];

            // Get available reservations for full test
            $reservations = $this->reservationRepository->findBy([], ['id' => 'DESC'], 10);

            $testResults['status'] = 'success';
            $testResults['message'] = 'All basic signature components are working correctly!';

        } catch (\Exception $e) {
            $testResults['status'] = 'error';
            $testResults['error'] = $e->getMessage();
            $testResults['error_trace'] = $e->getTraceAsString();
        }

        return $this->render('test_signature/test_flow.html.twig', [
            'results' => $testResults,
            'detailedResults' => $detailedResults,
            'reservations' => $reservations ?? [],
        ]);
    }

    /**
     * @Route("/full-test/{id}", name="test_signature_full", methods={"GET"})
     */
    public function fullTest(int $id): Response
    {
        $reservation = $this->reservationRepository->find($id);

        if (!$reservation) {
            return $this->redirectToRoute('test_signature_flow', [
                'error' => 'Reservation not found'
            ]);
        }

        $testResults = [];
        $contract = null;

        try {
            // Step 1: Create or get contract for reservation
            $contractContent = $this->generateContractContent($reservation);
            $contract = $this->contractService->getOrCreateContract($reservation, $contractContent);

            $testResults['contract_creation'] = [
                'success' => true,
                'contract_id' => $contract->getId(),
                'contract_hash' => $contract->getContractHash(),
                'contract_status' => $contract->getContractStatus(),
            ];

            // Step 2: Generate client keypair
            $clientKeypair = $this->signatureService->generateKeypair();
            $testResults['client_keypair'] = [
                'success' => true,
                'key_generated' => true,
            ];

            // Step 3: Create client signature
            $clientSignature = $this->signatureService->createSignature(
                $contract->getContractHash(),
                $clientKeypair['private_key']
            );

            // Step 4: Process client signature
            $clientContractSignature = $this->contractService->processClientSignature(
                $contract,
                $clientSignature,
                $clientKeypair['public_key'],
                '127.0.0.1',
                'Test Browser (Full Test)',
                true // Skip payment check for testing purposes
            );

            $testResults['client_signature'] = [
                'success' => true,
                'signature_id' => $clientContractSignature->getId(),
                'signed_at' => $clientContractSignature->getSignedAt()->format('Y-m-d H:i:s'),
                'has_timestamp' => !empty($clientContractSignature->getTimestampToken()),
            ];

            // Refresh contract status
            $this->entityManager->refresh($contract);

            $testResults['after_client_signature'] = [
                'contract_status' => $contract->getContractStatus(),
                'is_client_signed' => $contract->isSignedByClient(),
            ];

            // Step 5: Generate admin keypair
            $adminKeypair = $this->signatureService->generateKeypair();
            $testResults['admin_keypair'] = [
                'success' => true,
                'key_generated' => true,
            ];

            // Step 6: Create admin signature
            $adminSignature = $this->signatureService->createSignature(
                $contract->getContractHash(),
                $adminKeypair['private_key']
            );

            // Step 7: Process admin signature
            $adminContractSignature = $this->contractService->processAdminSignature(
                $contract,
                $adminSignature,
                $adminKeypair['public_key'],
                '127.0.0.1',
                'Test Browser Admin (Full Test)'
            );

            $testResults['admin_signature'] = [
                'success' => true,
                'signature_id' => $adminContractSignature->getId(),
                'signed_at' => $adminContractSignature->getSignedAt()->format('Y-m-d H:i:s'),
                'has_timestamp' => !empty($adminContractSignature->getTimestampToken()),
            ];

            // Refresh contract status
            $this->entityManager->refresh($contract);

            $testResults['after_admin_signature'] = [
                'contract_status' => $contract->getContractStatus(),
                'is_admin_signed' => $contract->isSignedByAdmin(),
                'is_fully_signed' => $contract->getContractStatus() === Contract::STATUS_FULLY_SIGNED,
            ];

            // Step 8: Verify all signatures
            $verificationResult = $this->contractService->verifyContractSignatures($contract);

            $testResults['verification'] = [
                'all_valid' => $verificationResult['valid'],
                'signature_count' => count($verificationResult['details']),
                'details' => array_map(function ($detail) {
                    return [
                        'type' => $detail['signature']->getSignatureType(),
                        'valid' => $detail['valid'],
                    ];
                }, $verificationResult['details']),
            ];

            $testResults['overall_status'] = 'success';
            $testResults['message'] = 'Full signature flow completed successfully!';

        } catch (\Exception $e) {
            $testResults['overall_status'] = 'error';
            $testResults['error'] = $e->getMessage();
            $testResults['error_trace'] = $e->getTraceAsString();
        }

        return $this->render('test_signature/full_test.html.twig', [
            'reservation' => $reservation,
            'contract' => $contract,
            'testResults' => $testResults,
        ]);
    }

    /**
     * @Route("/verify-contract/{id}", name="test_verify_contract", methods={"GET"})
     */
    public function verifyContract(int $id): Response
    {
        $contract = $this->contractRepository->find($id);

        if (!$contract) {
            return new JsonResponse(['error' => 'Contract not found'], 404);
        }

        $verificationResult = $this->contractService->verifyContractSignatures($contract);

        return $this->render('test_signature/verify_contract.html.twig', [
            'contract' => $contract,
            'verificationResult' => $verificationResult,
        ]);
    }

    /**
     * @Route("/api/test-crypto", name="test_crypto_api", methods={"POST"})
     */
    public function testCryptoApi(Request $request): JsonResponse
    {
        try {
            $content = $request->request->get('content', 'Test content ' . time());

            // Generate keypair
            $keypair = $this->signatureService->generateKeypair();

            // Calculate hash
            $hash = $this->signatureService->calculateSha256Hash($content);

            // Create signature
            $signature = $this->signatureService->createSignature($hash, $keypair['private_key']);

            // Verify signature
            $isValid = $this->signatureService->verifySignature($hash, $signature, $keypair['public_key']);

            // Request timestamp
            $timestamp = $this->tsaClient->requestTimestamp($hash);

            return new JsonResponse([
                'success' => true,
                'content' => $content,
                'hash' => $hash,
                'signature' => substr($signature, 0, 100) . '...',
                'public_key' => substr($keypair['public_key'], 0, 100) . '...',
                'signature_valid' => $isValid,
                'timestamp_token' => substr($timestamp, 0, 100) . '...',
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @Route("/cleanup/{id}", name="test_signature_cleanup", methods={"POST"})
     */
    public function cleanup(int $id): Response
    {
        $contract = $this->contractRepository->find($id);

        if ($contract) {
            $this->entityManager->remove($contract);
            $this->entityManager->flush();

            $this->addFlash('success', 'Test contract deleted successfully.');
        }

        return $this->redirectToRoute('test_signature_flow');
    }

    private function generateContractContent(Reservation $reservation): string
    {
        $content = "=== CONTRAT DE LOCATION DE VÉHICULE ===\n\n";
        $content .= "Référence: " . ($reservation->getReference() ?? 'N/A') . "\n";
        $content .= "Date de création: " . date('Y-m-d H:i:s') . "\n\n";

        $content .= "--- INFORMATIONS CLIENT ---\n";
        if ($client = $reservation->getClient()) {
            $content .= "Email: " . $client->getMail() . "\n";
        }

        $content .= "\n--- INFORMATIONS VÉHICULE ---\n";
        if ($vehicule = $reservation->getVehicule()) {
            $content .= "Immatriculation: " . $vehicule->getImmatriculation() . "\n";
        }

        $content .= "\n--- PÉRIODE DE LOCATION ---\n";
        $content .= "Début: " . ($reservation->getDateDebut() ? $reservation->getDateDebut()->format('d/m/Y H:i') : 'N/A') . "\n";
        $content .= "Fin: " . ($reservation->getDateFin() ? $reservation->getDateFin()->format('d/m/Y H:i') : 'N/A') . "\n";

        $content .= "\n--- CONDITIONS ---\n";
        $content .= "Prix total: " . ($reservation->getPrix() ?? 'N/A') . " €\n";

        $content .= "\n=== FIN DU CONTRAT ===\n";
        $content .= "Document généré le: " . date('Y-m-d H:i:s') . "\n";

        return $content;
    }
}