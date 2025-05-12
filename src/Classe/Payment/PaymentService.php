<?php

namespace App\Classe\Payment;


/**
 * Centralized service for processing payments
 */
class PaymentService
{
    private $factory;
    private $defaultProvider;

    public function __construct(PaymentProviderFactory $factory, string $defaultProvider = 'paypal')
    {
        $this->factory = $factory;
        $this->defaultProvider = $defaultProvider;
    }

    /**
     * Create a new payment order
     */
    public function createOrder(
        float $amount,
        array $orderDetails,
        string $provider = null
    ): PaymentResult {
        $provider = $provider ?: $this->defaultProvider;
        $paymentProvider = $this->factory->getProvider($provider);

        return $paymentProvider->createOrder($amount, $orderDetails);
    }

    /**
     * Process a payment with the specified provider (or default)
     */
    public function processPayment(
        float $amount,
        array $paymentDetails,
        string $provider = null
    ): PaymentResult {
        $provider = $provider ?: $this->defaultProvider;
        $paymentProvider = $this->factory->getProvider($provider);

        return $paymentProvider->processPayment($amount, $paymentDetails);
    }

    /**
     * Refund a payment with the specified provider
     */
    public function refundPayment(
        string $transactionId,
        ?float $amount = null,
        string $provider = null
    ): PaymentResult {
        $provider = $provider ?: $this->defaultProvider;
        $paymentProvider = $this->factory->getProvider($provider);

        return $paymentProvider->refundPayment($transactionId, $amount);
    }

    /**
     * Check status of a payment with the specified provider
     */
    public function getPaymentStatus(
        string $transactionId,
        string $provider = null
    ): PaymentResult {
        $provider = $provider ?: $this->defaultProvider;
        $paymentProvider = $this->factory->getProvider($provider);

        return $paymentProvider->getPaymentStatus($transactionId);
    }

    /**
     * Capture an authorized payment
     */
    public function capturePayment(
        string $orderId,
        string $provider = null
    ): PaymentResult {
        $provider = $provider ?: $this->defaultProvider;
        $paymentProvider = $this->factory->getProvider($provider);

        return $paymentProvider->capturePayment($orderId);
    }
}
