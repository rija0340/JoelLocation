<?php

namespace App\Service;

use App\Entity\Contract;
use App\Entity\ContractSignature;
use App\Entity\Reservation;
use App\Repository\ContractRepository;
use App\Repository\ContractSignatureRepository;
use Doctrine\ORM\EntityManagerInterface;

class ContractService
{
    private $contractRepository;
    private $contractSignatureRepository;
    private $entityManager;
    private $signatureService;
    private $tsaClient;

    public function __construct(
        ContractRepository $contractRepository,
        ContractSignatureRepository $contractSignatureRepository,
        EntityManagerInterface $entityManager,
        SignatureService $signatureService,
        TsaClient $tsaClient
    ) {
        $this->contractRepository = $contractRepository;
        $this->contractSignatureRepository = $contractSignatureRepository;
        $this->entityManager = $entityManager;
        $this->signatureService = $signatureService;
        $this->tsaClient = $tsaClient;
    }

    /**
     * Create a new contract for a reservation
     */
    public function createContract(Reservation $reservation, string $contractContent): Contract
    {
        $contract = new Contract();
        $contract->setReservation($reservation);

        // Calculate hash of the contract content
        $hash = $this->signatureService->calculateSha256Hash($contractContent);
        $contract->setContractHash($hash);
        $contract->setContractContent($contractContent);
        $contract->setContractStatus(Contract::STATUS_UNSIGNED);

        $this->contractRepository->save($contract, true);

        return $contract;
    }

    /**
     * Add a signature to a contract
     */
    public function addSignatureToContract(
        Contract $contract,
        string $signatureType,
        string $signatureData,
        string $publicKeyData,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $timestampToken = null,
        ?string $signatureImage = null
    ): ContractSignature {
        $contractSignature = $this->signatureService->createContractSignature(
            $contract,
            $signatureType,
            $signatureData,
            $publicKeyData,
            $ipAddress,
            $userAgent,
            $timestampToken,
            $signatureImage
        );

        // Save the signature to the database
        $this->contractSignatureRepository->save($contractSignature, true);

        // Update contract status based on signature type
        $this->updateContractStatus($contract, $signatureType);

        return $contractSignature;
    }

    /**
     * Process client signature
     */
    public function processClientSignature(
        Contract $contract,
        string $signatureData,
        string $publicKeyData,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        bool $skipPaymentCheck = false,
        ?string $signatureImage = null
    ): ContractSignature {
        // Note: Payment check has been removed - contracts can be signed without full payment

        if ($contract->isSignedByClient()) {
            throw new \Exception("Le client a déjà signé ce contrat.");
        }

        // Request timestamp from TSA
        $timestampToken = $this->tsaClient->requestTimestamp($contract->getContractHash());

        // Add signature to contract
        $signature = $this->addSignatureToContract(
            $contract,
            ContractSignature::TYPE_CLIENT,
            $signatureData,
            $publicKeyData,
            $ipAddress,
            $userAgent,
            $timestampToken,
            $signatureImage
        );

        return $signature;
    }

    /**
     * Process admin signature
     */
    public function processAdminSignature(
        Contract $contract,
        string $signatureData,
        string $publicKeyData,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $signatureImage = null
    ): ContractSignature {
        // Validation: Client must have signed first
        // if (!$contract->isSignedByClient()) {
        //     throw new \Exception("Le client doit signer le contrat avant l'administrateur.");
        // }

        if ($contract->isSignedByAdmin()) {
            throw new \Exception("L'administrateur a déjà signé ce contrat.");
        }

        // Request timestamp from TSA
        $timestampToken = $this->tsaClient->requestTimestamp($contract->getContractHash());

        // Add signature to contract
        $signature = $this->addSignatureToContract(
            $contract,
            ContractSignature::TYPE_ADMIN,
            $signatureData,
            $publicKeyData,
            $ipAddress,
            $userAgent,
            $timestampToken,
            $signatureImage
        );

        return $signature;
    }

    /**
     * Check if reservation is fully paid
     */
    public function isReservationPaid(Reservation $reservation): bool
    {
        $totalPrice = $reservation->getPrix();
        $totalPaid = $reservation->getSommePaiements();

        // Allow a small margin for float comparison errors (e.g. 0.01)
        return $totalPaid >= ($totalPrice - 0.01);
    }

    /**
     * Update the contract status based on signatures
     */
    public function updateContractStatus(Contract $contract, string $signatureType): void
    {
        $signatures = $contract->getSignatures();
        $hasClientSignature = false;
        $hasAdminSignature = false;

        foreach ($signatures as $signature) {
            if ($signature->getSignatureType() === ContractSignature::TYPE_CLIENT) {
                $hasClientSignature = true;
            } elseif ($signature->getSignatureType() === ContractSignature::TYPE_ADMIN) {
                $hasAdminSignature = true;
            }
        }

        if ($hasClientSignature && $hasAdminSignature) {
            $contract->setContractStatus(Contract::STATUS_FULLY_SIGNED);
        } elseif ($hasClientSignature) {
            $contract->setContractStatus(Contract::STATUS_CLIENT_SIGNED);
        } elseif ($hasAdminSignature) {
            $contract->setContractStatus(Contract::STATUS_ADMIN_SIGNED);
        } else {
            $contract->setContractStatus(Contract::STATUS_UNSIGNED);
        }

        $contract->setUpdatedAt(new \DateTime());
        $this->contractRepository->save($contract, true);
    }

    /**
     * Check if a reservation has a fully signed contract
     */
    public function isContractFullySigned(Reservation $reservation): bool
    {
        $contracts = $reservation->getContracts();

        foreach ($contracts as $contract) {
            if ($contract->getContractStatus() === Contract::STATUS_FULLY_SIGNED) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get or create a contract for a reservation
     */
    public function getOrCreateContract(Reservation $reservation, ?string $contractContent = null): Contract
    {
        $contracts = $reservation->getContracts();

        if (count($contracts) > 0) {
            // Return the most recent contract
            $latestContract = null;
            foreach ($contracts as $contract) {
                if (!$latestContract || $contract->getCreatedAt() > $latestContract->getCreatedAt()) {
                    $latestContract = $contract;
                }
            }
            return $latestContract;
        }

        // Create a new contract if none exists
        if (!$contractContent) {
            // Generate contract content based on reservation data
            $contractContent = $this->generateContractContent($reservation);
        }

        return $this->createContract($reservation, $contractContent);
    }

    /**
     * Generate default contract content from reservation data
     */
    private function generateContractContent(Reservation $reservation): string
    {
        // This is a simplified implementation
        // A real implementation would create the actual contract document
        $content = "CONTRAT DE LOCATION DE VEHICULE\n";
        $content .= "Référence: " . $reservation->getReference() . "\n";
        $content .= "Client: " . $reservation->getClient()->getMail() . "\n";
        $content .= "Véhicule: " . $reservation->getVehicule()->getImmatriculation() . "\n";
        $content .= "Période: " . $reservation->getDateDebut()->format('Y-m-d H:i') . " au " . $reservation->getDateFin()->format('Y-m-d H:i') . "\n";
        $content .= "Prix total: " . $reservation->getPrix() . "€\n";

        return $content;
    }

    /**
     * Verify all signatures on a contract
     */
    public function verifyContractSignatures(Contract $contract): array
    {
        return $this->signatureService->validateContract($contract);
    }
}