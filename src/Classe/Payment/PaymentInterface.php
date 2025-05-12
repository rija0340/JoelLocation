<?php


namespace App\Classe\Payment;

use App\Classe\Payment\PaymentResult;

/**
 * PaymentInterface defines the contract that all payment methods must implement
 */
interface PaymentInterface
{
    /**
     * Process a payment
     * 
     * @param float $amount The amount to charge
     * @param array $paymentDetails Payment method specific details
     * @return PaymentResult
     */
    public function processPayment(float $amount, array $paymentDetails): PaymentResult;

    /**
     * Refund a payment
     * 
     * @param string $transactionId The ID of the transaction to refund
     * @param float|null $amount The amount to refund (null for full refund)
     * @return PaymentResult
     */
    public function refundPayment(string $transactionId, ?float $amount = null): PaymentResult;

    /**
     * Check status of a payment
     * 
     * @param string $transactionId The transaction ID to check
     * @return PaymentResult
     */
    public function getPaymentStatus(string $transactionId): PaymentResult;

    public function createOrder(float $amount, array $orderDetails): PaymentResult;
}
