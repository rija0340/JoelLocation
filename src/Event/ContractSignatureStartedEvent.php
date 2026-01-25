<?php

namespace App\Event;

use App\Entity\Contract;
use Symfony\Contracts\EventDispatcher\Event;

class ContractSignatureStartedEvent extends Event
{
    public const NAME = 'contract.signature.started';

    private Contract $contract;
    private string $signatureType;
    private ?string $ipAddress;
    private ?string $userAgent;

    public function __construct(
        Contract $contract,
        string $signatureType,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ) {
        $this->contract = $contract;
        $this->signatureType = $signatureType;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
    }

    public function getContract(): Contract
    {
        return $this->contract;
    }

    public function getSignatureType(): string
    {
        return $this->signatureType;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }
}
