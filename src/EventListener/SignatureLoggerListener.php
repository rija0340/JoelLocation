<?php

namespace App\EventListener;

use App\Event\ContractSignatureStartedEvent;
use App\Event\ContractSignatureCompletedEvent;
use App\Service\SignatureLoggerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener for comprehensive signature logging
 * Listens to signature events and logs them using SignatureLoggerService
 */
class SignatureLoggerListener implements EventSubscriberInterface
{
    private $signatureLogger;

    public function __construct(SignatureLoggerService $signatureLogger)
    {
        $this->signatureLogger = $signatureLogger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContractSignatureStartedEvent::NAME => 'onSignatureStarted',
            ContractSignatureCompletedEvent::NAME => 'onSignatureCompleted',
        ];
    }

    public function onSignatureStarted(ContractSignatureStartedEvent $event): void
    {
        $this->signatureLogger->logSignatureStarted(
            $event->getContract(),
            $event->getSignatureType(),
            $event->getIpAddress(),
            $event->getUserAgent()
        );
    }

    public function onSignatureCompleted(ContractSignatureCompletedEvent $event): void
    {
        $this->signatureLogger->logSignatureCompleted(
            $event->getContract(),
            $event->getContractSignature(),
            $event->getSignatureType()
        );

        // Also log the contract status
        $this->signatureLogger->logContractSignatureStatus($event->getContract());
    }
}
