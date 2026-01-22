<?php

namespace App\EventListener;

use App\Event\ContractSignedByClientEvent;
use App\Service\EmailManagerService;
use Psr\Log\LoggerInterface;

class ContractSignedByClientEventListener
{
    private EmailManagerService $emailManagerService;
    private LoggerInterface $logger;

    public function __construct(EmailManagerService $emailManagerService, LoggerInterface $logger)
    {
        $this->emailManagerService = $emailManagerService;
        $this->logger = $logger;
    }

    public function onContractSignedByClient(ContractSignedByClientEvent $event): void
    { 
        try {
            $this->emailManagerService->notifyAdminContractSigned($event->getReservation());
            $this->logger->info('Admin notified about contract signature', [
                'reservation_id' => $event->getReservation()->getId(),
                'reference' => $event->getReservation()->getReference()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to notify admin about contract signature', [
                'reservation_id' => $event->getReservation()->getId(),
                'error' => $e->getMessage()
            ]);
        }
    }
}