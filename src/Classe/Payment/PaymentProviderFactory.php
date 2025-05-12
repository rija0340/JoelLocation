<?php

namespace App\Classe\Payment;

use App\Classe\Payment\PaymentInterface;
use App\Classe\Payment\StripePaymentProvider;
use App\Classe\Payment\PaypalPaymentProvider;

/**
 * Factory to create payment providers
 */
class PaymentProviderFactory
{
    private $containers = [];

    public function __construct(array $providerConfigs, \Psr\Log\LoggerInterface $logger)
    {
        // Set up the containers with the configurations
        foreach ($providerConfigs as $provider => $config) {
            $this->containers[$provider] = [
                'config' => $config,
                'logger' => $logger,
                'instance' => null,
            ];
        }
    }

    /**
     * Get a payment provider
     */
    public function getProvider(string $provider): PaymentInterface
    {
        if (!isset($this->containers[$provider])) {
            throw new \InvalidArgumentException(sprintf('Payment provider "%s" is not supported', $provider));
        }

        if ($this->containers[$provider]['instance'] === null) {
            $this->containers[$provider]['instance'] = $this->createProvider($provider);
        }

        return $this->containers[$provider]['instance'];
    }

    /**
     * Create a new instance of a payment provider
     */
    private function createProvider(string $provider): PaymentInterface
    {
        $container = $this->containers[$provider];
        $config = $container['config'];
        $logger = $container['logger'];

        switch ($provider) {
            // case 'stripe':
            //     return new StripePaymentProvider($logger, $config['api_key']);

            case 'paypal':
                return new PaypalPaymentProvider(
                    $logger,
                    $config['client_id'],
                    $config['client_secret'],
                    $config['sandbox'] ?? false
                );

                // Add more cases for additional providers

            default:
                throw new \InvalidArgumentException(sprintf('Payment provider "%s" is not supported', $provider));
        }
    }
}
