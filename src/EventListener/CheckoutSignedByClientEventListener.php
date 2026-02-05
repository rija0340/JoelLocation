<?php

namespace App\EventListener;

use App\Event\CheckoutSignedByClientEvent;
use App\Service\EmailManagerService;
use Psr\Log\LoggerInterface;

class CheckoutSignedByClientEventListener
{
    private $emailManagerService;
    private $logger;

    public function __construct(EmailManagerService $emailManagerService, LoggerInterface $logger)
    {
        $this->emailManagerService = $emailManagerService;
        $this->logger = $logger;
    }

    public function onCheckoutSignedByClient(CheckoutSignedByClientEvent $event): void
    {
        try {
            $this->emailManagerService->notifyAdminCheckoutSigned($event->getReservation());
            $this->logger->info('Admin notified about checkout signature', [
                'reservation_id' => $event->getReservation()->getId(),
                'reference' => $event->getReservation()->getReference()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to notify admin about checkout signature', [
                'reservation_id' => $event->getReservation()->getId(),
                'error' => $e->getMessage()
            ]);
        }
    }
}
