<?php

namespace App\Service;

use App\Repository\TarifsV2CellRepository;
use App\Repository\PricingIntervalRepository;
use App\Repository\MarqueRepository;
use App\Repository\ModeleRepository;
use App\Service\TarifsV2MatrixService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TarifsV2ImportExportService
{
    private $cellRepo;
    private $intervalRepo;
    private $marqueRepo;
    private $modeleRepo;
    private $matrixService;
    private $em;

    public function __construct(
        TarifsV2CellRepository $cellRepo,
        PricingIntervalRepository $intervalRepo,
        MarqueRepository $marqueRepo,
        ModeleRepository $modeleRepo,
        TarifsV2MatrixService $matrixService,
        EntityManagerInterface $em
    ) {
        $this->cellRepo = $cellRepo;
        $this->intervalRepo = $intervalRepo;
        $this->marqueRepo = $marqueRepo;
        $this->modeleRepo = $modeleRepo;
        $this->matrixService = $matrixService;
        $this->em = $em;
    }

    /**
     * Export all data to CSV (Excel-compatible)
     */
    public function exportToCsv(): StreamedResponse
    {
        $response = new StreamedResponse(function() {
            $handle = fopen('php://output', 'w');
            
            // BOM for Excel UTF-8
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($handle, ['Marque', 'Modèle', 'Mois', 'Intervalle', 'Prix (€)'], ';');
            
            // Get all data
            $cells = $this->cellRepo->findAll();
            
            foreach ($cells as $cell) {
                fputcsv($handle, [
                    $cell->getMarque()->getLibelle(),
                    $cell->getModele()->getLibelle(),
                    $cell->getMonth(),
                    $cell->getPricingInterval()->getLabel(),
                    $cell->getPrice()
                ], ';');
            }
            
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="tarifs-v2-' . date('Y-m-d') . '.csv"');

        return $response;
    }

    /**
     * Import from CSV
     * @return array ['success' => int, 'errors' => array]
     */
    public function importFromCsv(string $csvContent, bool $updateExisting = true): array
    {
        $results = [
            'success' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => []
        ];

        $lines = explode("\n", $csvContent);
        $header = null;
        $lineNumber = 0;

        // Ensure intervals exist
        $this->intervalRepo->initializeDefaults();

        foreach ($lines as $line) {
            $lineNumber++;
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }

            // Parse CSV
            $data = str_getcsv($line, ';');
            
            // Skip header row
            if ($header === null) {
                $header = $data;
                continue;
            }

            try {
                $result = $this->processCsvRow($data, $updateExisting);
                $results['success']++;
                if ($result === 'created') {
                    $results['created']++;
                } else {
                    $results['updated']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'line' => $lineNumber,
                    'data' => $data,
                    'error' => $e->getMessage()
                ];
            }
        }

        $this->em->flush();

        return $results;
    }

    /**
     * Process a single CSV row
     */
    private function processCsvRow(array $data, bool $updateExisting): string
    {
        if (count($data) < 5) {
            throw new \Exception('Invalid row format');
        }

        $marqueName = trim($data[0]);
        $modeleName = trim($data[1]);
        $month = trim($data[2]);
        $intervalLabel = trim($data[3]);
        $price = str_replace(',', '.', trim($data[4]));

        // Find entities
        $marque = $this->marqueRepo->findOneBy(['libelle' => $marqueName]);
        $modele = $this->modeleRepo->findOneBy(['libelle' => $modeleName]);

        if (!$marque) {
            throw new \Exception("Marque not found: $marqueName");
        }
        if (!$modele) {
            throw new \Exception("Modele not found: $modeleName");
        }

        // Find interval by label
        $intervals = $this->intervalRepo->findAll();
        $interval = null;
        foreach ($intervals as $i) {
            if ($i->getLabel() === $intervalLabel || $i->getDisplayLabel() === $intervalLabel) {
                $interval = $i;
                break;
            }
        }

        if (!$interval) {
            throw new \Exception("Interval not found: $intervalLabel");
        }

        // Validate month
        if (!in_array($month, TarifsV2MatrixService::getMonths())) {
            throw new \Exception("Invalid month: $month");
        }

        // Check if cell exists
        $cell = $this->cellRepo->findOneByVehicleMonthInterval($marque, $modele, $month, $interval);

        if ($cell) {
            if (!$updateExisting) {
                throw new \Exception('Cell already exists (skipped)');
            }
            $cell->setPrice($price);
            return 'updated';
        } else {
            $cell = new \App\Entity\TarifsV2Cell();
            $cell->setMarque($marque);
            $cell->setModele($modele);
            $cell->setMonth($month);
            $cell->setPricingInterval($interval);
            $cell->setPrice($price);
            $this->em->persist($cell);
            return 'created';
        }
    }

    /**
     * Generate CSV template
     */
    public function generateCsvTemplate(): string
    {
        $csv = "marque;modele;month;interval;price\n";
        
        // Add example rows
        $marques = $this->marqueRepo->findAll();
        $intervals = $this->intervalRepo->findAllOrdered();
        $months = TarifsV2MatrixService::getMonths();
        
        if (count($marques) > 0 && count($intervals) > 0) {
            $marque = $marques[0];
            // Get first modele for this marque
            $modeles = $this->modeleRepo->findBy(['marque' => $marque]);
            
            if (count($modeles) > 0) {
                $modele = $modeles[0];
                $interval = $intervals[0];
                
                // Add 3 example rows
                $csv .= $marque->getLibelle() . ";" . $modele->getLibelle() . ";" . $months[0] . ";" . $interval->getLabel() . ";50.00\n";
                $csv .= $marque->getLibelle() . ";" . $modele->getLibelle() . ";" . $months[1] . ";" . $interval->getLabel() . ";45.00\n";
                $csv .= $marque->getLibelle() . ";" . $modele->getLibelle() . ";" . $months[2] . ";" . $interval->getLabel() . ";45.00\n";
            }
        }
        
        return $csv;
    }

    /**
     * Get import preview (first 10 rows)
     */
    public function getImportPreview(string $csvContent): array
    {
        $lines = explode("\n", $csvContent);
        $preview = [];
        $header = null;
        $count = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $data = str_getcsv($line, ';');
            
            if ($header === null) {
                $header = $data;
                continue;
            }

            if ($count >= 10) {
                break;
            }

            $preview[] = [
                'marque' => $data[0] ?? '',
                'modele' => $data[1] ?? '',
                'month' => $data[2] ?? '',
                'interval' => $data[3] ?? '',
                'price' => $data[4] ?? ''
            ];
            $count++;
        }

        return $preview;
    }
}
