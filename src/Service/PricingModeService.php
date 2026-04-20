<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Service to manage pricing mode toggle (V1 vs V2)
 */
class PricingModeService
{
    private $params;
    private $configFile;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        // Store pricing mode in a file outside version control
        $this->configFile = dirname(dirname(__DIR__)) . '/config/pricing_mode.json';
    }

    /**
     * Check if V2 pricing is active
     */
    public function isV2Active(): bool
    {
        if (!file_exists($this->configFile)) {
            return false; // Default to V1
        }

        $config = json_decode(file_get_contents($this->configFile), true);
        return $config['active_model'] === 'v2';
    }

    /**
     * Activate V2 pricing
     */
    public function activateV2(): bool
    {
        return $this->setActiveModel('v2');
    }

    /**
     * Activate V1 pricing (original bracket system)
     */
    public function activateV1(): bool
    {
        return $this->setActiveModel('v1');
    }

    /**
     * Get current active model
     */
    public function getActiveModel(): string
    {
        return $this->isV2Active() ? 'v2' : 'v1';
    }

    /**
     * Set active pricing model
     */
    private function setActiveModel(string $model): bool
    {
        $config = [
            'active_model' => $model,
            'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        $result = file_put_contents(
            $this->configFile,
            json_encode($config, JSON_PRETTY_PRINT)
        );

        return $result !== false;
    }
}
