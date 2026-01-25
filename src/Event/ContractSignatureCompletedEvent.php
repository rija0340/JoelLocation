<?php

namespace App\Event;

use App\Entity\Contract;
use App\Entity\ContractSignature;
use Symfony\Contracts\EventDispatcher\Event;

class ContractSignatureCompletedEvent extends Event
{
    public const NAME = 'contract.signature.completed';

    private Contract $contract;
    private ContractSignature $contractSignature;
    private string $signatureType;

    public function __construct(
        Contract $contract,
        ContractSignature $contractSignature,
        string $signatureType
    ) {
        $this->contract = $contract;
        $this->contractSignature = $contractSignature;
        $this->signatureType = $signatureType;
    }

    public function getContract(): Contract
    {
        return $this->contract;
    }

    public function getContractSignature(): ContractSignature
    {
        return $this->contractSignature;
    }

    public function getSignatureType(): string
    {
        return $this->signatureType;
    }
}
