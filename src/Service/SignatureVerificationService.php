<?php

namespace App\Service;

use App\Entity\Contract;
use App\Entity\ContractSignature;
use App\Repository\ContractRepository;
use App\Repository\ContractSignatureRepository;

class SignatureVerificationService
{
    private ContractRepository $contractRepository;
    private ContractSignatureRepository $signatureRepository;
    private SignatureService $signatureService;
    private TsaClient $tsaClient;

    public function __construct(
        ContractRepository $contractRepository,
        ContractSignatureRepository $signatureRepository,
        SignatureService $signatureService,
        TsaClient $tsaClient
    ) {
        $this->contractRepository = $contractRepository;
        $this->signatureRepository = $signatureRepository;
        $this->signatureService = $signatureService;
        $this->tsaClient = $tsaClient;
    }

    /**
     * Verify a single signature
     */
    public function verifySignature(ContractSignature $signature): bool
    {
        $contract = $signature->getContract();
        $contractHash = $contract->getContractHash();
        $signatureData = $signature->getSignatureData();
        $publicKey = $signature->getPublicKeyData();

        return $this->signatureService->verifySignature($contractHash, $signatureData, $publicKey);
    }

    /**
     * Verify all signatures on a contract
     */
    public function verifyContract(Contract $contract): array
    {
        $results = [
            'contract_id' => $contract->getId(),
            'valid' => true,
            'signatures' => [],
            'timestamp_verification' => []
        ];

        // Verify each signature
        foreach ($contract->getSignatures() as $signature) {
            $signatureValid = $this->verifySignature($signature);
            $timestampValid = true;

            if ($signature->getTimestampToken()) {
                $timestampValid = $this->tsaClient->verifyTimestamp(
                    $signature->getTimestampToken(),
                    $contract->getContractHash()
                );
            }

            $results['signatures'][] = [
                'id' => $signature->getId(),
                'type' => $signature->getSignatureType(),
                'valid' => $signatureValid,
                'timestamp_token' => $signature->getTimestampToken(),
                'timestamp_valid' => $timestampValid,
                'signed_at' => $signature->getSignedAt()
            ];

            // If any signature is invalid, the whole contract is invalid
            if (!$signatureValid || !$timestampValid) {
                $results['valid'] = false;
            }
        }

        return $results;
    }

    /**
     * Comprehensive verification of contract signatures with detailed information
     */
    public function getDetailedVerificationReport(Contract $contract): array
    {
        $report = [
            'contract' => [
                'id' => $contract->getId(),
                'status' => $contract->getContractStatus(),
                'hash' => $contract->getContractHash(),
                'created_at' => $contract->getCreatedAt(),
            ],
            'verification_result' => $this->verifyContract($contract),
            'signatures' => []
        ];

        foreach ($contract->getSignatures() as $signature) {
            $signatureReport = [
                'id' => $signature->getId(),
                'type' => $signature->getSignatureType(),
                'valid' => $signature->getSignatureValid(),
                'signed_at' => $signature->getSignedAt(),
                'ip_address' => $signature->getIpAddress(),
                'user_agent' => $signature->getUserAgent(),
                'timestamp_token' => $signature->getTimestampToken(),
                'timestamp_verified_at' => $signature->getTimestampVerifiedAt(),
            ];

            $report['signatures'][] = $signatureReport;
        }

        return $report;
    }

    /**
     * Verify that the contract hasn't been tampered with since signing
     */
    public function verifyContractIntegrity(Contract $contract, ?string $originalContent = null): bool
    {
        if (!$originalContent) {
            $originalContent = $contract->getContractContent();
        }

        // Recalculate the hash of the original content
        $calculatedHash = $this->signatureService->calculateSha256Hash($originalContent);

        // Compare with the stored hash
        return hash_equals($contract->getContractHash(), $calculatedHash);
    }

    /**
     * Validate all contracts for a reservation
     */
    public function validateReservationContracts(int $reservationId): array
    {
        $contracts = $this->contractRepository->findBy(['reservation' => $reservationId]);
        
        $results = [
            'reservation_id' => $reservationId,
            'contracts' => [],
            'all_valid' => true
        ];

        foreach ($contracts as $contract) {
            $verification = $this->verifyContract($contract);
            $results['contracts'][] = [
                'contract_id' => $contract->getId(),
                'status' => $contract->getContractStatus(),
                'verification' => $verification
            ];

            if (!$verification['valid']) {
                $results['all_valid'] = false;
            }
        }

        return $results;
    }
}