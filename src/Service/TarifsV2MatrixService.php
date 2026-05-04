<?php

namespace App\Service;

use App\Entity\TarifsV2Cell;
use App\Entity\Marque;
use App\Entity\Modele;
use App\Entity\User;
use App\Repository\TarifsV2CellRepository;
use App\Repository\PricingIntervalRepository;
use Doctrine\ORM\EntityManagerInterface;

class TarifsV2MatrixService
{
    private $cellRepo;
    private $intervalRepo;
    private $historyLogger;
    private $em;

    private const MONTHS = [
        'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
    ];

    public function __construct(
        TarifsV2CellRepository $cellRepo,
        PricingIntervalRepository $intervalRepo,
        TarifsV2HistoryLogger $historyLogger,
        EntityManagerInterface $em
    ) {
        $this->cellRepo = $cellRepo;
        $this->intervalRepo = $intervalRepo;
        $this->historyLogger = $historyLogger;
        $this->em = $em;
    }

    /**
     * Get matrix data for a vehicle
     */
    public function getVehicleMatrix(Marque $marque, Modele $modele): array
    {
        // Ensure intervals exist
        $this->intervalRepo->initializeDefaults();
        
        $intervals = $this->intervalRepo->findAllOrdered();
        $cells = $this->cellRepo->findByVehicle($marque, $modele);
        
        // Build matrix structure
        $matrix = [];
        foreach ($intervals as $interval) {
            $row = [
                'interval' => $interval,
                'months' => []
            ];
            
            foreach (self::MONTHS as $month) {
                $cell = null;
                foreach ($cells as $c) {
                    if ($c->getMonth() === $month && $c->getPricingInterval()->getId() === $interval->getId()) {
                        $cell = $c;
                        break;
                    }
                }
                
                $row['months'][$month] = [
                    'cell' => $cell,
                    'price' => $cell ? (float) $cell->getPrice() : null,
                    'hasPrice' => $cell !== null
                ];
            }
            
            $matrix[] = $row;
        }
        
        return [
            'marque' => $marque,
            'modele' => $modele,
            'matrix' => $matrix,
            'months' => self::MONTHS
        ];
    }

    /**
     * Save multiple cells with history logging to file
     */
    public function saveCells(array $changes, User $user, ?string $ipAddress = null): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'deleted' => 0,
            'errors' => []
        ];

        foreach ($changes as $change) {
            try {
                $result = $this->saveSingleCell($change, $user, $ipAddress);
                if ($result['action'] === 'created') {
                    $results['created']++;
                } elseif ($result['action'] === 'deleted') {
                    $results['deleted']++;
                } elseif ($result['action'] === 'updated') {
                    $results['updated']++;
                }
                // 'none' action doesn't increment any counter
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'data' => $change,
                    'error' => $e->getMessage()
                ];
            }
        }

        $this->em->flush();

        return $results;
    }

    /**
     * Save a single cell (or delete if price is null)
     */
    private function saveSingleCell(array $data, User $user, ?string $ipAddress): array
    {
        $marque = $this->em->getRepository(Marque::class)->find($data['marque_id']);
        $modele = $this->em->getRepository(Modele::class)->find($data['modele_id']);
        $interval = $this->intervalRepo->find($data['interval_id']);
        
        if (!$marque || !$modele || !$interval) {
            throw new \Exception('Invalid references');
        }

        $month = $data['month'];
        $price = $data['price'];
        $vehicleName = $marque->getLibelle() . ' ' . $modele->getLibelle();

        // Check if cell exists
        $cell = $this->cellRepo->findOneByVehicleMonthInterval($marque, $modele, $month, $interval);

        // Handle deletion (null price)
        if ($price === null) {
            if ($cell) {
                $oldPrice = $cell->getPrice();
                // Log deletion
                $this->historyLogger->logChange(
                    $vehicleName,
                    $month,
                    $interval->getLabel(),
                    $oldPrice ? (float) $oldPrice : null,
                    null,
                    $user,
                    $ipAddress
                );
                $this->em->remove($cell);
                return ['action' => 'deleted', 'cell' => null];
            }
            // No cell to delete, nothing to do
            return ['action' => 'none', 'cell' => null];
        }

        // Normal save/update
        $priceStr = (string) $price;
        $oldPrice = null;

        if ($cell) {
            $oldPrice = $cell->getPrice();
            $cell->setPrice($priceStr);
            $action = 'updated';
        } else {
            $cell = new TarifsV2Cell();
            $cell->setMarque($marque);
            $cell->setModele($modele);
            $cell->setMonth($month);
            $cell->setPricingInterval($interval);
            $cell->setPrice($priceStr);
            $this->em->persist($cell);
            $action = 'created';
        }

        // Log to file if price changed
        if ($oldPrice !== $priceStr) {
            $this->historyLogger->logChange(
                $vehicleName,
                $month,
                $interval->getLabel(),
                $oldPrice ? (float) $oldPrice : null,
                (float) $price,
                $user,
                $ipAddress
            );
        }

        return ['action' => $action, 'cell' => $cell];
    }

    /**
     * Copy prices from one month to all others for a vehicle
     */
    public function copyMonthToAll(Marque $marque, Modele $modele, string $sourceMonth, User $user, ?string $ipAddress = null): array
    {
        $changes = [];
        $intervals = $this->intervalRepo->findAllOrdered();
        $affectedCount = 0;
        
        foreach ($intervals as $interval) {
            // Get source cell
            $sourceCell = $this->cellRepo->findOneByVehicleMonthInterval($marque, $modele, $sourceMonth, $interval);
            
            if ($sourceCell) {
                // Copy to all other months
                foreach (self::MONTHS as $month) {
                    if ($month !== $sourceMonth) {
                        $changes[] = [
                            'marque_id' => $marque->getId(),
                            'modele_id' => $modele->getId(),
                            'interval_id' => $interval->getId(),
                            'month' => $month,
                            'price' => $sourceCell->getPrice()
                        ];
                        $affectedCount++;
                    }
                }
            }
        }
        
        $results = $this->saveCells($changes, $user, $ipAddress);
        
        // Log bulk operation
        if ($affectedCount > 0) {
            $vehicleName = $marque->getLibelle() . ' ' . $modele->getLibelle();
            $this->historyLogger->logBulkOperation(
                "Copy $sourceMonth to all months",
                $vehicleName,
                $affectedCount,
                $user,
                $ipAddress
            );
        }
        
        return $results;
    }

    /**
     * Apply percentage change to prices for a vehicle, optionally filtered by intervals and months.
     *
     * @param int[]|null $intervalIds If provided, only cells matching these interval IDs are affected
     * @param string[]|null $targetMonths If provided, only cells matching these month names are affected
     */
    public function applyPercentageChange(
        Marque $marque, 
        Modele $modele, 
        float $percentage, 
        User $user, 
        ?string $ipAddress = null,
        ?array $intervalIds = null,
        ?array $targetMonths = null
    ): array {
        $changes = [];
        $cells = $this->cellRepo->findByVehicle($marque, $modele);
        
        // Build interval label list for logging when filtering
        $filteredIntervalLabels = [];
        
        foreach ($cells as $cell) {
            // Filter by interval IDs if provided
            if ($intervalIds !== null && !empty($intervalIds)) {
                if (!in_array($cell->getPricingInterval()->getId(), $intervalIds)) {
                    continue;
                }
            }
            
            // Filter by months if provided
            if ($targetMonths !== null && !empty($targetMonths)) {
                if (!in_array($cell->getMonth(), $targetMonths)) {
                    continue;
                }
            }
            
            $currentPrice = (float) $cell->getPrice();
            $newPrice = $currentPrice * (1 + $percentage / 100);
            
            $changes[] = [
                'marque_id' => $marque->getId(),
                'modele_id' => $modele->getId(),
                'interval_id' => $cell->getPricingInterval()->getId(),
                'month' => $cell->getMonth(),
                'price' => round($newPrice, 2)
            ];
        }
        
        $results = $this->saveCells($changes, $user, $ipAddress);
        
        // Build detailed log message
        if (count($changes) > 0) {
            $vehicleName = $marque->getLibelle() . ' ' . $modele->getLibelle();
            
            $logMessage = "Apply {$percentage}% change";
            
            // Add interval filter info to log
            if ($intervalIds !== null && !empty($intervalIds)) {
                $intervalLabels = [];
                foreach ($intervalIds as $id) {
                    $interval = $this->intervalRepo->find($id);
                    if ($interval) {
                        $intervalLabels[] = $interval->getLabel();
                    }
                }
                if (!empty($intervalLabels)) {
                    $logMessage .= " sur " . implode(', ', $intervalLabels);
                }
            }
            
            // Add month filter info to log
            if ($targetMonths !== null && !empty($targetMonths)) {
                $logMessage .= " pour " . implode(', ', $targetMonths);
            }
            
            $this->historyLogger->logBulkOperation(
                $logMessage,
                $vehicleName,
                count($changes),
                $user,
                $ipAddress
            );
        }
        
        return $results;
    }

    /**
     * Copy all prices from one vehicle to another
     */
    public function copyVehicleToVehicle(
        Marque $sourceMarque, 
        Modele $sourceModele,
        Marque $targetMarque, 
        Modele $targetModele,
        User $user, 
        ?string $ipAddress = null
    ): array {
        $changes = [];
        $cells = $this->cellRepo->findByVehicle($sourceMarque, $sourceModele);
        
        foreach ($cells as $cell) {
            $changes[] = [
                'marque_id' => $targetMarque->getId(),
                'modele_id' => $targetModele->getId(),
                'interval_id' => $cell->getPricingInterval()->getId(),
                'month' => $cell->getMonth(),
                'price' => $cell->getPrice()
            ];
        }
        
        $results = $this->saveCells($changes, $user, $ipAddress);
        
        // Log bulk operation
        if (count($changes) > 0) {
            $sourceName = $sourceMarque->getLibelle() . ' ' . $sourceModele->getLibelle();
            $targetName = $targetMarque->getLibelle() . ' ' . $targetModele->getLibelle();
            $this->historyLogger->logBulkOperation(
                "Copy from $sourceName to $targetName",
                $targetName,
                count($changes),
                $user,
                $ipAddress
            );
        }
        
        return $results;
    }

    /**
     * Get comparison data
     */
    public function getComparisonData(string $month, int $intervalId): array
    {
        $interval = $this->intervalRepo->find($intervalId);
        if (!$interval) {
            return [];
        }
        
        $cells = $this->cellRepo->findForComparison($month, $interval);
        
        $data = [];
        foreach ($cells as $cell) {
            $data[] = [
                'vehicle' => $cell->getVehicleName(),
                'marque' => $cell->getMarque()->getLibelle(),
                'modele' => $cell->getModele()->getLibelle(),
                'marque_id' => $cell->getMarque()->getId(),
                'modele_id' => $cell->getModele()->getId(),
                'price' => (float) $cell->getPrice()
            ];
        }
        
        // Sort by price
        usort($data, function($a, $b) {
            return $a['price'] <=> $b['price'];
        });
        
        return $data;
    }

    /**
     * Get months list
     */
    public static function getMonths(): array
    {
        return self::MONTHS;
    }
}
