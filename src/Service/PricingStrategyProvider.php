<?php

namespace App\Service;

use App\Service\Contract\PricingStrategyInterface;
use App\Service\PricingModeService;
use App\Service\Strategy\V1PricingStrategy;
use App\Service\Strategy\V2PricingStrategy;
use App\Repository\PricingIntervalRepository;
use App\Repository\TarifsV2CellRepository;
use App\Repository\TarifsRepository;
use App\Service\TarifsHelper;
use App\Service\DateHelper;

/**
 * Pricing Strategy Provider
 * 
 * Factory that provides the appropriate pricing strategy based on
 * the current configuration (V1 or V2).
 * 
 * Usage:
 *   $strategy = $provider->getStrategy();
 *   $price = $strategy->calculate($vehicule, $dateDepart, $dateRetour);
 */
class PricingStrategyProvider
{
    private $modeService;
    private $intervalRepo;
    private $cellRepo;
    private $tarifsRepo;
    private $tarifsHelper;
    private $dateHelper;

    public function __construct(
        PricingModeService $modeService,
        PricingIntervalRepository $intervalRepo,
        TarifsV2CellRepository $cellRepo,
        TarifsRepository $tarifsRepo,
        TarifsHelper $tarifsHelper,
        DateHelper $dateHelper
    ) {
        $this->modeService = $modeService;
        $this->intervalRepo = $intervalRepo;
        $this->cellRepo = $cellRepo;
        $this->tarifsRepo = $tarifsRepo;
        $this->tarifsHelper = $tarifsHelper;
        $this->dateHelper = $dateHelper;
    }

    /**
     * Get the active pricing strategy based on configuration
     */
    public function getStrategy(): PricingStrategyInterface
    {
        if ($this->modeService->isV2Active()) {
            return new V2PricingStrategy(
                $this->intervalRepo,
                $this->cellRepo,
                $this->dateHelper
            );
        }

        return new V1PricingStrategy($this->tarifsHelper);
    }

    /**
     * Get the name of the active strategy
     */
    public function getActiveStrategyName(): string
    {
        return $this->getStrategy()->getName();
    }

    /**
     * Check if V2 is the active strategy
     */
    public function isV2Active(): bool
    {
        return $this->modeService->isV2Active();
    }
}
