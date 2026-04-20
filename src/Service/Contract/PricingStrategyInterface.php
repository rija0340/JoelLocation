<?php

namespace App\Service\Contract;

use App\Entity\Vehicule;

/**
 * Interface for pricing calculation strategies
 */
interface PricingStrategyInterface
{
    /**
     * Calculate the vehicle price based on the strategy
     * 
     * @param Vehicule $vehicule The vehicle to price
     * @param \DateTime $dateDepart Start date of rental
     * @param \DateTime $dateRetour End date of rental
     * @return float The calculated price
     */
    public function calculate(Vehicule $vehicule, \DateTime $dateDepart, \DateTime $dateRetour): float;

    /**
     * Return the strategy name for logging/debugging
     * 
     * @return string
     */
    public function getName(): string;
}
