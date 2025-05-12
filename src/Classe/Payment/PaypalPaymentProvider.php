<?php

namespace App\Classe\Payment;

use App\Classe\Payment\PaymentResult;
use App\Classe\Payment\AbstractPaymentProvider;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Example implementation for PayPal
 */
class PaypalPaymentProvider extends AbstractPaymentProvider implements PaymentInterface
{
    private $paypalClient;
    private $clientId;
    private $clientSecret;
    private $isSandbox;
    private $accessToken;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        string $clientId,
        string $clientSecret,
        bool $isSandbox = false
    ) {
        parent::__construct($logger);
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->isSandbox = $isSandbox;
        // Initialize PayPal client here
    }

    private function getBaseUrl(): string
    {
        return $this->isSandbox
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }


    private function generateAccessToken(): string
    {
        try {
            $client = HttpClient::create([
                'verify_peer' => false, // Only for development/debugging
                'verify_host' => false  // Only for development/debugging
            ]);

            $response = $client->request(
                'POST',
                $this->getBaseUrl() . '/v1/oauth2/token',
                [
                    'auth_basic' => [$this->clientId, $this->clientSecret],
                    'headers' => [
                        'Accept' => 'application/json',
                        'Accept-Language' => 'en_US',
                    ],
                    'body' => ['grant_type' => 'client_credentials'],
                    'timeout' => 30
                ]
            );

            $data = $response->toArray();

            if (!isset($data['access_token'])) {
                throw new \Exception('No access token in PayPal response');
            }

            $this->accessToken = $data['access_token'];
            return $this->accessToken;
        } catch (\Exception $e) {
            $this->logger->error('Failed to generate PayPal access token: ' . $e->getMessage());
            throw $e;
        }
    }
    public function createOrder(float $amount, array $orderDetails): PaymentResult
    {
        try {
            if (!isset($orderDetails['currency'])) {
                throw new \InvalidArgumentException('Currency is required');
            }

            $client = HttpClient::create([
                'verify_peer' => false,
                'verify_host' => false
            ]);

            $accessToken = $this->generateAccessToken();

            // Ensure URLs are absolute
            $returnUrl = $orderDetails['return_url'];
            $cancelUrl = $orderDetails['cancel_url'];

            // If URLs don't start with http:// or https://, they're relative
            if (!preg_match('/^https?:\/\//', $returnUrl)) {
                $returnUrl = 'https://' . $_SERVER['HTTP_HOST'] . $returnUrl;
            }
            if (!preg_match('/^https?:\/\//', $cancelUrl)) {
                $cancelUrl = 'https://' . $_SERVER['HTTP_HOST'] . $cancelUrl;
            }

            $payload = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => $orderDetails['currency'],
                        'value' => number_format($amount, 2, '.', ''),
                    ],
                    'description' => $orderDetails['description'] ?? 'Order payment',
                    'reference_id' => $orderDetails['devisId'] ?? null,
                ]],
                'application_context' => [
                    'brand_name' => 'Joel Location',
                    'landing_page' => 'NO_PREFERENCE',
                    'user_action' => 'PAY_NOW',
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl
                ]
            ];

            // Add debug logging
            $this->logger->debug('PayPal API Request', [
                'url' => $this->getBaseUrl() . '/v2/checkout/orders',
                'payload' => $payload,
                'access_token' => substr($accessToken, 0, 10) . '...' // Log only part of the token for security
            ]);

            $response = $client->request(
                'POST',
                $this->getBaseUrl() . '/v2/checkout/orders',
                [
                    'headers' => [
                        'Authorization' => "Bearer {$accessToken}",
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Prefer' => 'return=representation'
                    ],
                    'json' => $payload,
                    'timeout' => 30, // Increase timeout to 30 seconds
                    'max_redirects' => 5
                ]
            );

            // Add response debugging
            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false); // false to get raw content even if not 2xx

            $this->logger->debug('PayPal API Response', [
                'status_code' => $statusCode,
                'content' => $content
            ]);

            if ($statusCode >= 400) {
                throw new \Exception("PayPal API error: " . $content);
            }

            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON response from PayPal");
            }

            $this->logPaymentActivity(
                'order_created',
                $data['id'],
                $amount,
                ['currency' => $orderDetails['currency']]
            );

            return new PaymentResult(
                PaymentResult::STATUS_SUCCESS,
                $data['id'],
                null,
                $data
            );
        } catch (\Exception $e) {
            $this->logger->error('PayPal order creation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return new PaymentResult(
                PaymentResult::STATUS_FAILED,
                'error_' . time(),
                $e->getMessage()
            );
        }
    }

    public function capturePayment(string $orderId): PaymentResult
    {
        try {
            $client = HttpClient::create();
            $accessToken = $this->generateAccessToken();

            $response = $client->request(
                'POST',
                $this->getBaseUrl() . "/v2/checkout/orders/{$orderId}/capture",
                [
                    'headers' => [
                        'Authorization' => "Bearer {$accessToken}",
                        'Content-Type' => 'application/json',
                    ]
                ]
            );

            $data = $response->toArray();

            return new PaymentResult(
                PaymentResult::STATUS_SUCCESS,
                $data['id'],
                null,
                $data
            );
        } catch (\Exception $e) {
            $this->logger->error('PayPal payment capture failed: ' . $e->getMessage());
            return new PaymentResult(
                PaymentResult::STATUS_FAILED,
                null,
                $e->getMessage()
            );
        }
    }

    public function processPayment(float $amount, array $paymentDetails): PaymentResult
    {
        try {
            // PayPal implementation would go here
            // This is just a placeholder
            $transactionId = 'pp_' . uniqid();

            $this->logPaymentActivity('processed', $transactionId, $amount);

            return new PaymentResult(
                PaymentResult::STATUS_SUCCESS,
                $transactionId
            );
        } catch (\Exception $e) {
            $this->logger->error('PayPal payment failed: ' . $e->getMessage());

            return new PaymentResult(
                PaymentResult::STATUS_FAILED,
                'none',
                $e->getMessage()
            );
        }
    }

    public function refundPayment(string $transactionId, ?float $amount = null): PaymentResult
    {
        // PayPal refund implementation
        // Placeholder
        $refundId = 'ref_' . uniqid();
        return new PaymentResult(PaymentResult::STATUS_SUCCESS, $refundId);
    }

    public function getPaymentStatus(string $transactionId): PaymentResult
    {
        // PayPal status check implementation
        // Placeholder
        return new PaymentResult(PaymentResult::STATUS_SUCCESS, $transactionId);
    }
}
