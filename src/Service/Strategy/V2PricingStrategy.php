<?php

namespace App\Service\Strategy;

use App\Entity\Vehicule;
use App\Entity\Marque;
use App\Entity\Modele;
use App\Entity\PricingInterval;
use App\Entity\TarifsV2Cell;
use App\Repository\PricingIntervalRepository;
use App\Repository\TarifsV2CellRepository;
use App\Service\Contract\PricingStrategyInterface;
use App\Service\DateHelper;

/**
 * V2 Pricing Strategy - Custom interval-based pricing
 * 
 * This strategy uses the new pricing_interval and tarifs_v2_cell tables
 * to calculate vehicle prices based on custom day ranges.
 */
class V2PricingStrategy implements PricingStrategyInterface
{
    private $intervalRepo;
    private $cellRepo;
    private $dateHelper;

    public function __construct(
        PricingIntervalRepository $intervalRepo,
        TarifsV2CellRepository $cellRepo,
        DateHelper $dateHelper
    ) {
        $this->intervalRepo = $intervalRepo;
        $this->cellRepo = $cellRepo;
        $this->dateHelper = $dateHelper;
    }

    /**
     * Calculate vehicle price using V2 custom intervals
     */
    public function calculate(Vehicule $vehicule, \DateTime $dateDepart, \DateTime $dateRetour): float
    {
        $duree = $this->dateHelper->calculDureeInclusif($dateDepart, $dateRetour);
        $marque = $vehicule->getMarque();
        $modele = $vehicule->getModele();

        // Duration <= 30 days: simple single month lookup
        if ($duree <= 30) {
            return $this->calculateSingleMonth($marque, $modele, $dateDepart, $duree);
        }

        // Duration > 30 days: multi-month calculation
        return $this->calculateMultiMonth($marque, $modele, $dateDepart, $dateRetour);
    }

    /**
     * Return the strategy name
     */
    public function getName(): string
    {
        return 'V2 (Custom Ranges)';
    }

    /**
     * Calculate price for a single month rental
     */
    private function calculateSingleMonth(Marque $marque, Modele $modele, \DateTime $date, int $days): float
    {
        $month = $this->dateHelper->getMonthFullName($date);
        $interval = $this->findSmallestContainingInterval($days);
        
        if (!$interval) {
            return 0;
        }

        $cell = $this->cellRepo->findOneByVehicleMonthInterval($marque, $modele, $month, $interval);
        
        if (!$cell) {
            return 0;
        }

        $price = $cell->getPrice();
        return $price !== null && $price !== '' ? (float) $price * $days : 0;
    }

    /**
     * Calculate price for multi-month rental
     */
    private function calculateMultiMonth(Marque $marque, Modele $modele, \DateTime $dateDepart, \DateTime $dateRetour): float
    {
        $total = 0;
        $dateCourante = clone $dateDepart;

        while ($dateCourante < $dateRetour) {
            $month = $this->dateHelper->getMonthFullName($dateCourante);
            $finDuMois = new \DateTime($dateCourante->format('Y-m-t'));
            $dateFin = ($finDuMois < $dateRetour) ? $finDuMois : $dateRetour;
            $joursDansPeriode = $this->dateHelper->calculDureeInclusif($dateCourante, $dateFin);

            // Find the smallest interval that contains this number of days
            $interval = $this->findSmallestContainingInterval($joursDansPeriode);
            
            if ($interval) {
                $cell = $this->cellRepo->findOneByVehicleMonthInterval($marque, $modele, $month, $interval);
                
                if ($cell) {
                    $price = $cell->getPrice();
                    if ($price !== null && $price !== '') {
                        $total += (float) $price * $joursDansPeriode;
                    }
                }
            }

            // Move to next month (1st day of next month)
            $dateCourante = new \DateTime($finDuMois->format('Y-m-d') . ' +1 day');
        }

        return $total;
    }

    /**
     * Find the smallest interval that contains the given number of days
     * 
     * Logic: For a given number of days, find the interval with the smallest
     * range that still covers those days. This gives the best price for the customer.
     * 
     * Example: 5 days
     * - Matches: 3-6 jours (covers 5 days)
     * - Result: 3-6 jours (selected because it's the smallest range that contains 5)
     * 
     * Example: 2 days
     * - Matches: 1-2 jours
     * - Result: 1-2 jours
     * 
     * Example: 31 days
     * - Matches: 31+ jours
     * - Result: 31+ jours
     */
    private function findSmallestContainingInterval(int $days): ?PricingInterval
    {
        // Get all intervals ordered by max_days ASC (smallest first)
        $intervals = $this->intervalRepo->createQueryBuilder('pi')
            ->where('pi.minDays <= :days')
            ->andWhere('pi.maxDays IS NULL OR pi.maxDays >= :days')
            ->setParameter('days', $days)
            ->orderBy('pi.maxDays', 'ASC')  // Smallest max_days first
            ->addOrderBy('pi.minDays', 'ASC')
            ->getQuery()
            ->getResult();

        // Return the first match (smallest interval)
        return $intervals[0] ?? null;
    }
}
