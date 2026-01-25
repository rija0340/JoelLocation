<?php

namespace App\Service;

use App\Entity\Contract;
use App\Entity\ContractSignature;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Comprehensive logging service for contract signatures
 * Logs all signature events with complete audit trail
 */
class SignatureLoggerService
{
    private $signatureLogger;
    private $environment;

    public function __construct(LoggerInterface $signatureLogger, string $environment = 'dev')
    {
        $this->signatureLogger = $signatureLogger;
        $this->environment = $environment;
    }

    /**
     * Log when signature process starts
     */
    public function logSignatureStarted(
        Contract $contract,
        string $signatureType,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        $this->signatureLogger->info('Contract signature process started', [
            'contract_id' => $contract->getId(),
            'reservation_id' => $contract->getReservation()->getId(),
            'reservation_reference' => $contract->getReservation()->getReference(),
            'signature_type' => $signatureType,
            'ip_address' => $ipAddress,
            'user_agent' => substr($userAgent ?? '', 0, 255), // Limit length
            'contract_hash_preview' => substr($contract->getContractHash(), 0, 16),
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s.u'),
        ]);
    }

    /**
     * Log signature creation and cryptographic operations
     */
    public function logSignatureCreated(
        Contract $contract,
        ContractSignature $contractSignature,
        string $cryptoSignaturePreview,
        ?string $publicKeyPreview = null
    ): void {
        $this->signatureLogger->info('Cryptographic signature created', [
            'contract_id' => $contract->getId(),
            'contract_signature_id' => $contractSignature->getId(),
            'reservation_reference' => $contract->getReservation()->getReference(),
            'signature_type' => $contractSignature->getSignatureType(),
            'crypto_signature_preview' => $cryptoSignaturePreview,
            'public_key_preview' => $publicKeyPreview ? substr($publicKeyPreview, 0, 32) : null,
            'signature_size_bytes' => strlen($contractSignature->getSignatureData()),
            'public_key_size_bytes' => strlen($contractSignature->getPublicKeyData()),
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s.u'),
        ]);
    }

    /**
     * Log timestamp token reception
     */
    public function logTimestampTokenReceived(
        Contract $contract,
        ContractSignature $contractSignature,
        ?string $timestampTokenPreview = null
    ): void {
        $this->signatureLogger->info('Timestamp token received from TSA', [
            'contract_id' => $contract->getId(),
            'contract_signature_id' => $contractSignature->getId(),
            'reservation_reference' => $contract->getReservation()->getReference(),
            'tsa_service' => 'FreeTSA',
            'timestamp_token_preview' => $timestampTokenPreview ? substr($timestampTokenPreview, 0, 32) : 'none',
            'timestamp_verified_at' => $contractSignature->getTimestampVerifiedAt() ?
                $contractSignature->getTimestampVerifiedAt()->format('Y-m-d H:i:s.u') : null,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s.u'),
        ]);
    }

    /**
     * Log successful signature completion
     */
    public function logSignatureCompleted(
        Contract $contract,
        ContractSignature $contractSignature,
        string $signatureType
    ): void {
        $this->signatureLogger->info('Contract signature completed successfully', [
            'contract_id' => $contract->getId(),
            'contract_signature_id' => $contractSignature->getId(),
            'reservation_id' => $contract->getReservation()->getId(),
            'reservation_reference' => $contract->getReservation()->getReference(),
            'signature_type' => $signatureType,
            'contract_status' => $contract->getContractStatus(),
            'signed_at' => $contractSignature->getSignedAt()->format('Y-m-d H:i:s.u'),
            'ip_address' => $contractSignature->getIpAddress(),
            'country_hint' => $this->getCountryFromIp($contractSignature->getIpAddress()),
            'has_timestamp' => !empty($contractSignature->getTimestampToken()),
            'signature_valid' => $contractSignature->getSignatureValid(),
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s.u'),
        ]);
    }

    /**
     * Log signature verification attempt
     */
    public function logSignatureVerificationAttempt(
        Contract $contract,
        ContractSignature $contractSignature,
        bool $verificationResult
    ): void {
        $level = $verificationResult ? 'info' : 'warning';

        $this->signatureLogger->$level('Signature verification attempt', [
            'contract_id' => $contract->getId(),
            'contract_signature_id' => $contractSignature->getId(),
            'reservation_reference' => $contract->getReservation()->getReference(),
            'signature_type' => $contractSignature->getSignatureType(),
            'verification_result' => $verificationResult ? 'valid' : 'invalid',
            'signed_at' => $contractSignature->getSignedAt()->format('Y-m-d H:i:s.u'),
            'verified_at' => (new \DateTime())->format('Y-m-d H:i:s.u'),
            'time_since_signature' => $this->getTimeDifference(
                $contractSignature->getSignedAt(),
                new \DateTime()
            ),
        ]);
    }

    /**
     * Log contract integrity check
     */
    public function logContractIntegrityCheck(
        Contract $contract,
        bool $integrityValid,
        ?string $originalHash = null
    ): void {
        $level = $integrityValid ? 'info' : 'error';

        $this->signatureLogger->$level('Contract integrity check', [
            'contract_id' => $contract->getId(),
            'reservation_id' => $contract->getReservation()->getId(),
            'reservation_reference' => $contract->getReservation()->getReference(),
            'integrity_valid' => $integrityValid,
            'current_hash_preview' => substr($contract->getContractHash(), 0, 16),
            'original_hash_preview' => $originalHash ? substr($originalHash, 0, 16) : null,
            'checked_at' => (new \DateTime())->format('Y-m-d H:i:s.u'),
        ]);
    }

    /**
     * Log contract full signature status
     */
    public function logContractSignatureStatus(Contract $contract): void
    {
        $signatures = $contract->getSignatures();
        $clientSignature = null;
        $adminSignature = null;

        foreach ($signatures as $sig) {
            if ($sig->getSignatureType() === 'client') {
                $clientSignature = $sig;
            } elseif ($sig->getSignatureType() === 'admin') {
                $adminSignature = $sig;
            }
        }

        $this->signatureLogger->info('Contract signature status updated', [
            'contract_id' => $contract->getId(),
            'reservation_id' => $contract->getReservation()->getId(),
            'reservation_reference' => $contract->getReservation()->getReference(),
            'contract_status' => $contract->getContractStatus(),
            'is_fully_signed' => $contract->getContractStatus() === 'fully_signed',
            'client_signed' => $clientSignature !== null,
            'client_signed_at' => $clientSignature ? $clientSignature->getSignedAt()->format('Y-m-d H:i:s') : null,
            'client_ip' => $clientSignature ? $clientSignature->getIpAddress() : null,
            'admin_signed' => $adminSignature !== null,
            'admin_signed_at' => $adminSignature ? $adminSignature->getSignedAt()->format('Y-m-d H:i:s') : null,
            'admin_ip' => $adminSignature ? $adminSignature->getIpAddress() : null,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s.u'),
        ]);
    }

    /**
     * Log signature errors
     */
    public function logSignatureError(
        Contract $contract,
        string $signatureType,
        \Exception $exception,
        ?string $context = null
    ): void {
        $this->signatureLogger->error('Signature process error', [
            'contract_id' => $contract->getId(),
            'reservation_id' => $contract->getReservation()->getId(),
            'reservation_reference' => $contract->getReservation()->getReference(),
            'signature_type' => $signatureType,
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine(),
            'context' => $context,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s.u'),
        ]);
    }

    /**
     * Log TSA communication errors
     */
    public function logTsaError(
        Contract $contract,
        \Exception $exception,
        string $operation = 'request'
    ): void {
        $this->signatureLogger->error('TSA communication error', [
            'contract_id' => $contract->getId(),
            'reservation_reference' => $contract->getReservation()->getReference(),
            'tsa_operation' => $operation,
            'tsa_service' => 'FreeTSA',
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s.u'),
        ]);
    }

    /**
     * Log signature duplication attempt (security)
     */
    public function logDuplicateSignatureAttempt(
        Contract $contract,
        string $signatureType
    ): void {
        $this->signatureLogger->warning('Duplicate signature attempt detected', [
            'contract_id' => $contract->getId(),
            'reservation_id' => $contract->getReservation()->getId(),
            'reservation_reference' => $contract->getReservation()->getReference(),
            'signature_type' => $signatureType,
            'existing_signature_count' => count($contract->getSignatures()),
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s.u'),
        ]);
    }

    /**
     * Log access control violations
     */
    public function logAccessDenied(
        int $contractId,
        int $reservationId,
        string $reason,
        ?string $ipAddress = null
    ): void {
        $this->signatureLogger->warning('Signature access denied', [
            'contract_id' => $contractId,
            'reservation_id' => $reservationId,
            'denial_reason' => $reason,
            'ip_address' => $ipAddress,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s.u'),
        ]);
    }

    /**
     * Log batch verification operations
     */
    public function logBatchVerification(
        int $reservationId,
        int $totalContracts,
        int $validContracts,
        int $invalidContracts
    ): void {
        $this->signatureLogger->info('Batch signature verification completed', [
            'reservation_id' => $reservationId,
            'total_contracts' => $totalContracts,
            'valid_contracts' => $validContracts,
            'invalid_contracts' => $invalidContracts,
            'success_rate' => $totalContracts > 0 ? round(($validContracts / $totalContracts) * 100, 2) : 0,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s.u'),
        ]);
    }

    /**
     * Helper: Get country from IP (basic, for audit trail)
     */
    private function getCountryFromIp(?string $ip): ?string
    {
        if (!$ip) {
            return null;
        }

        // Simple check for common patterns (not a full GeoIP implementation)
        if (strpos($ip, '127.0.0.1') === 0 || strpos($ip, '::1') === 0) {
            return 'LOCAL';
        }

        // Could integrate MaxMind GeoIP2 here for production
        return null;
    }

    /**
     * Helper: Calculate time difference between two dates
     */
    private function getTimeDifference(\DateTime $from, \DateTime $to): string
    {
        $interval = $from->diff($to);

        if ($interval->d > 0) {
            return $interval->d . ' day(s)';
        }
        if ($interval->h > 0) {
            return $interval->h . ' hour(s)';
        }
        if ($interval->i > 0) {
            return $interval->i . ' minute(s)';
        }

        return $interval->s . ' second(s)';
    }
}
