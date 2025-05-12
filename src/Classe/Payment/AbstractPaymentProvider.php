<?php

namespace App\Classe\Payment;

/**
 * Abstract base class that handles common functionality
 */
abstract class AbstractPaymentProvider
{
    protected $logger;

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log payment activity
     */
    protected function logPaymentActivity(string $action, string $transactionId, float $amount = null): void
    {
        $this->logger->info(sprintf(
            'Payment %s: %s, Amount: %s, Provider: %s',
            $action,
            $transactionId,
            $amount ? number_format($amount, 2) : 'N/A',
            static::class
        ));
    }
}
