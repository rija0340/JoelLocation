<?php

namespace App\Service\Strategy;

use App\Entity\Vehicule;
use App\Service\Contract\PricingStrategyInterface;
use App\Service\TarifsHelper;

/**
 * V1 Pricing Strategy - Legacy bracket-based pricing
 * 
 * This strategy wraps the existing TarifsHelper logic to maintain
 * backward compatibility while allowing future removal.
 */
class V1PricingStrategy implements PricingStrategyInterface
{
    private $tarifsHelper;

    public function __construct(TarifsHelper $tarifsHelper)
    {
        $this->tarifsHelper = $tarifsHelper;
    }

    /**
     * Calculate vehicle price using V1 bracket system
     */
    public function calculate(Vehicule $vehicule, \DateTime $dateDepart, \DateTime $dateRetour): float
    {
        // Delegate to existing TarifsHelper logic
        // Note: TarifsHelper already checks isV2Active internally,
        // but since this strategy is only returned when V1 is active,
        // it will use the V1 calculation path
        return $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);
    }

    /**
     * Return the strategy name
     */
    public function getName(): string
    {
        return 'V1 (Brackets)';
    }
}
