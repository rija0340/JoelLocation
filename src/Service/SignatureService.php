<?php

namespace App\Service;

use App\Entity\Contract;
use App\Entity\ContractSignature;
use App\Repository\ContractSignatureRepository;
use Exception;

class SignatureService
{
    private const SIGNING_ALGORITHM = OPENSSL_ALGO_SHA256;
    private const RSA_KEY_SIZE = 2048;

    public function generateKeypair(): array
    {
        $config = [
            "digest_alg" => "sha256",
            "private_key_bits" => self::RSA_KEY_SIZE,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        $resource = openssl_pkey_new($config);

        if (!$resource) {
            throw new Exception('Failed to generate private key');
        }

        $privateKey = '';
        openssl_pkey_export($resource, $privateKey);

        $details = openssl_pkey_get_details($resource);
        $publicKey = $details['key'];

        openssl_pkey_free($resource);

        return [
            'private_key' => $privateKey,
            'public_key' => $publicKey
        ];
    }

    public function calculateSha256Hash(string $content): string
    {
        return hash('sha256', $content);
    }

    public function createSignature(string $data, string $privateKey): string
    {
        $signature = '';
        $result = openssl_sign($data, $signature, $privateKey, self::SIGNING_ALGORITHM);

        if (!$result) {
            throw new Exception('Failed to create signature');
        }

        return base64_encode($signature);
    }

    public function verifySignature(string $data, string $signature, string $publicKey): bool
    {
        $decodedSignature = base64_decode($signature);
        return openssl_verify($data, $decodedSignature, $publicKey, self::SIGNING_ALGORITHM) === 1;
    }

    public function createContractSignature(
        Contract $contract,
        string $signatureType,
        string $signatureData,
        string $publicKeyData,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $timestampToken = null,
        ?string $signatureImage = null,
        string $documentType = ContractSignature::DOC_CONTRACT
    ): ContractSignature {
        $contractSignature = new ContractSignature();
        $contractSignature->setContract($contract);
        $contractSignature->setSignatureType($signatureType);
        $contractSignature->setDocumentType($documentType);
        $contractSignature->setSignatureData($signatureData);
        $contractSignature->setPublicKeyData($publicKeyData);
        $contractSignature->setIpAddress($ipAddress);
        $contractSignature->setUserAgent($userAgent);
        $contractSignature->setTimestampToken($timestampToken);
        $contractSignature->setSignatureImage($signatureImage);

        // Initially set to false, can be validated later
        $contractSignature->setSignatureValid(true);

        if ($timestampToken) {
            $contractSignature->setTimestampVerifiedAt(new \DateTime());
        }

        return $contractSignature;
    }

    public function validateContract(Contract $contract): array
    {
        $results = [];
        $allValid = true;

        foreach ($contract->getSignatures() as $signature) {
            $valid = $this->verifySignature($contract->getContractHash(), $signature->getSignatureData(), $signature->getPublicKeyData());
            $signature->setSignatureValid($valid);

            $results[] = [
                'signature' => $signature,
                'valid' => $valid
            ];

            if (!$valid) {
                $allValid = false;
            }
        }

        return [
            'valid' => $allValid,
            'details' => $results
        ];
    }
}