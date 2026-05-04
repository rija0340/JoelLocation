<?php

namespace App\Controller\Api;

use App\Repository\PricingIntervalRepository;
use App\Repository\MarqueRepository;
use App\Repository\ModeleRepository;
use App\Service\TarifsV2MatrixService;
use App\Service\TarifsV2ImportExportService;
use App\Service\TarifsV2HistoryLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/tarifs-v2")
 */
class TarifsV2ApiController extends AbstractController
{
    private $intervalRepo;
    private $marqueRepo;
    private $modeleRepo;
    private $matrixService;
    private $importExportService;
    private $historyLogger;
    private $em;

    public function __construct(
        PricingIntervalRepository $intervalRepo,
        MarqueRepository $marqueRepo,
        ModeleRepository $modeleRepo,
        TarifsV2MatrixService $matrixService,
        TarifsV2ImportExportService $importExportService,
        TarifsV2HistoryLogger $historyLogger,
        EntityManagerInterface $em
    ) {
        $this->intervalRepo = $intervalRepo;
        $this->marqueRepo = $marqueRepo;
        $this->modeleRepo = $modeleRepo;
        $this->matrixService = $matrixService;
        $this->importExportService = $importExportService;
        $this->historyLogger = $historyLogger;
        $this->em = $em;
    }

    /**
     * @Route("/intervals", name="api_tarifs_v2_intervals", methods={"GET"})
     */
    public function getIntervals(): JsonResponse
    {
        $this->intervalRepo->initializeDefaults();
        $intervals = $this->intervalRepo->findAllOrdered();
        
        $data = [];
        foreach ($intervals as $interval) {
            $data[] = [
                'id' => $interval->getId(),
                'label' => $interval->getLabel(),
                'display_label' => $interval->getDisplayLabel(),
                'min_days' => $interval->getMinDays(),
                'max_days' => $interval->getMaxDays(),
                'sort_order' => $interval->getSortOrder()
            ];
        }
        
        return new JsonResponse($data);
    }

    /**
     * @Route("/intervals", name="api_tarifs_v2_intervals_create", methods={"POST"})
     */
    public function createInterval(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $interval = new \App\Entity\PricingInterval();
        $interval->setMinDays($data['min_days']);
        $interval->setMaxDays($data['max_days'] ?? null);
        $interval->setLabel($data['label'] ?? $interval->generateLabel());
        $interval->setSortOrder($this->intervalRepo->getNextSortOrder());
        
        $this->em->persist($interval);
        $this->em->flush();
        
        return new JsonResponse([
            'success' => true,
            'id' => $interval->getId(),
            'message' => 'Interval created'
        ]);
    }

    /**
     * @Route("/intervals/{id}", name="api_tarifs_v2_intervals_delete", methods={"DELETE"})
     */
    public function deleteInterval(int $id): JsonResponse
    {
        $interval = $this->intervalRepo->find($id);
        
        if (!$interval) {
            return new JsonResponse(['error' => 'Interval not found'], 404);
        }
        
        $this->em->remove($interval);
        $this->em->flush();
        
        return new JsonResponse(['success' => true, 'message' => 'Interval deleted']);
    }

    /**
     * @Route("/vehicle/{marqueId}/{modeleId}", name="api_tarifs_v2_vehicle_matrix", methods={"GET"})
     */
    public function getVehicleMatrix(int $marqueId, int $modeleId): JsonResponse
    {
        $marque = $this->marqueRepo->find($marqueId);
        $modele = $this->modeleRepo->find($modeleId);
        
        if (!$marque || !$modele) {
            return new JsonResponse(['error' => 'Vehicle not found'], 404);
        }
        
        $matrix = $this->matrixService->getVehicleMatrix($marque, $modele);
        
        // Format for JSON
        $formatted = [
            'marque' => ['id' => $marque->getId(), 'name' => $marque->getLibelle()],
            'modele' => ['id' => $modele->getId(), 'name' => $modele->getLibelle()],
            'months' => $matrix['months'],
            'rows' => []
        ];
        
        foreach ($matrix['matrix'] as $row) {
            $rowData = [
                'interval' => [
                    'id' => $row['interval']->getId(),
                    'label' => $row['interval']->getLabel(),
                    'display_label' => $row['interval']->getDisplayLabel()
                ],
                'months' => []
            ];
            
            foreach ($row['months'] as $month => $cellData) {
                $rowData['months'][$month] = [
                    'cell_id' => $cellData['cell'] ? $cellData['cell']->getId() : null,
                    'price' => $cellData['price'],
                    'has_price' => $cellData['hasPrice']
                ];
            }
            
            $formatted['rows'][] = $rowData;
        }
        
        return new JsonResponse($formatted);
    }

    /**
     * @Route("/bulk-save", name="api_tarifs_v2_bulk_save", methods={"POST"})
     */
    public function bulkSave(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $changes = $data['changes'] ?? [];
        
        if (empty($changes)) {
            return new JsonResponse(['error' => 'No changes provided'], 400);
        }
        
        $user = $this->getUser();
        $ipAddress = $request->getClientIp();
        
        try {
            $results = $this->matrixService->saveCells($changes, $user, $ipAddress);
            
            return new JsonResponse([
                'success' => true,
                'created' => $results['created'],
                'updated' => $results['updated'],
                'deleted' => $results['deleted'],
                'errors' => $results['errors']
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/copy-month", name="api_tarifs_v2_copy_month", methods={"POST"})
     */
    public function copyMonthToAll(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $marque = $this->marqueRepo->find($data['marque_id'] ?? 0);
        $modele = $this->modeleRepo->find($data['modele_id'] ?? 0);
        $sourceMonth = $data['source_month'] ?? '';
        
        if (!$marque || !$modele) {
            return new JsonResponse(['error' => 'Vehicle not found'], 404);
        }
        
        $user = $this->getUser();
        $ipAddress = $request->getClientIp();
        
        try {
            $results = $this->matrixService->copyMonthToAll($marque, $modele, $sourceMonth, $user, $ipAddress);
            
            return new JsonResponse([
                'success' => true,
                'message' => "Copied $sourceMonth to all months",
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/apply-percentage", name="api_tarifs_v2_apply_percentage", methods={"POST"})
     */
    public function applyPercentage(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $marque = $this->marqueRepo->find($data['marque_id'] ?? 0);
        $modele = $this->modeleRepo->find($data['modele_id'] ?? 0);
        $percentage = (float) ($data['percentage'] ?? 0);
        $intervalIds = $data['intervals_ids'] ?? null;
        $targetMonths = $data['months'] ?? null;
        
        if (!$marque || !$modele) {
            return new JsonResponse(['error' => 'Vehicle not found'], 404);
        }
        
        $user = $this->getUser();
        $ipAddress = $request->getClientIp();
        
        try {
            $results = $this->matrixService->applyPercentageChange(
                $marque, $modele, $percentage, $user, $ipAddress,
                $intervalIds, $targetMonths
            );
            
            return new JsonResponse([
                'success' => true,
                'message' => "Applied $percentage% change",
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/copy-vehicle", name="api_tarifs_v2_copy_vehicle", methods={"POST"})
     */
    public function copyVehicle(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $sourceMarque = $this->marqueRepo->find($data['source_marque_id'] ?? 0);
        $sourceModele = $this->modeleRepo->find($data['source_modele_id'] ?? 0);
        $targetMarque = $this->marqueRepo->find($data['target_marque_id'] ?? 0);
        $targetModele = $this->modeleRepo->find($data['target_modele_id'] ?? 0);
        
        if (!$sourceMarque || !$sourceModele || !$targetMarque || !$targetModele) {
            return new JsonResponse(['error' => 'Vehicle not found'], 404);
        }
        
        $user = $this->getUser();
        $ipAddress = $request->getClientIp();
        
        try {
            $results = $this->matrixService->copyVehicleToVehicle(
                $sourceMarque, $sourceModele,
                $targetMarque, $targetModele,
                $user, $ipAddress
            );
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Copied prices from source to target vehicle',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/comparison", name="api_tarifs_v2_comparison", methods={"GET"})
     */
    public function getComparison(Request $request): JsonResponse
    {
        $month = $request->query->get('month', 'Janvier');
        $intervalId = (int) $request->query->get('interval_id', 0);
        
        if (!$intervalId) {
            // Get first interval
            $this->intervalRepo->initializeDefaults();
            $intervals = $this->intervalRepo->findAllOrdered();
            if (count($intervals) > 0) {
                $intervalId = $intervals[0]->getId();
            }
        }
        
        $data = $this->matrixService->getComparisonData($month, $intervalId);
        
        return new JsonResponse([
            'month' => $month,
            'interval_id' => $intervalId,
            'data' => $data
        ]);
    }

    /**
     * @Route("/history", name="api_tarifs_v2_history", methods={"GET"})
     */
    public function getHistory(Request $request): JsonResponse
    {
        $limit = (int) $request->query->get('limit', 100);
        
        // Parse date filters
        $from = null;
        $to = null;
        
        if ($request->query->get('date_from')) {
            $from = \DateTime::createFromFormat('Y-m-d', $request->query->get('date_from'));
        }
        if ($request->query->get('date_to')) {
            $to = \DateTime::createFromFormat('Y-m-d', $request->query->get('date_to'));
            $to->setTime(23, 59, 59);
        }
        
        $history = $this->historyLogger->getHistory($limit, $from, $to);
        $statistics = $this->historyLogger->getStatistics();
        
        // Format history for JSON
        $formattedHistory = [];
        foreach ($history as $entry) {
            $formattedHistory[] = [
                'date' => $entry['date_formatted'],
                'user' => $entry['user'],
                'vehicle' => $entry['vehicle'],
                'month' => $entry['month'],
                'interval' => $entry['interval'],
                'old_price' => $entry['old_price'],
                'new_price' => $entry['new_price'],
                'difference' => $entry['difference'],
                'percentage' => $entry['percentage']
            ];
        }
        
        return new JsonResponse([
            'data' => $formattedHistory,
            'statistics' => $statistics
        ]);
    }

    /**
     * @Route("/history/download", name="api_tarifs_v2_history_download", methods={"GET"})
     */
    public function downloadHistoryLog(): Response
    {
        $logFile = $this->historyLogger->getLogFilePath();
        
        if (!file_exists($logFile)) {
            return new Response('No history log file found', 404);
        }
        
        $response = new StreamedResponse(function() use ($logFile) {
            readfile($logFile);
        });
        
        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set('Content-Disposition', 'attachment; filename="tarifs_v2_history.log"');
        
        return $response;
    }

    /**
     * @Route("/export", name="api_tarifs_v2_export", methods={"GET"})
     */
    public function export()
    {
        return $this->importExportService->exportToCsv();
    }

    /**
     * @Route("/import", name="api_tarifs_v2_import", methods={"POST"})
     */
    public function import(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        
        if (!$file) {
            return new JsonResponse(['error' => 'No file provided'], 400);
        }
        
        $csvContent = file_get_contents($file->getPathname());
        
        try {
            $results = $this->importExportService->importFromCsv($csvContent, true);
            
            return new JsonResponse([
                'success' => true,
                'created' => $results['created'],
                'updated' => $results['updated'],
                'errors' => $results['errors']
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/import-preview", name="api_tarifs_v2_import_preview", methods={"POST"})
     */
    public function importPreview(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        
        if (!$file) {
            return new JsonResponse(['error' => 'No file provided'], 400);
        }
        
        $csvContent = file_get_contents($file->getPathname());
        $preview = $this->importExportService->getImportPreview($csvContent);
        
        return new JsonResponse(['preview' => $preview]);
    }

    /**
     * @Route("/vehicles", name="api_tarifs_v2_vehicles", methods={"GET"})
     */
    public function getVehicles(): JsonResponse
    {
        $marques = $this->marqueRepo->findAll();
        $vehicles = [];
        
        foreach ($marques as $marque) {
            $modeles = $this->modeleRepo->findBy(['marque' => $marque]);
            foreach ($modeles as $modele) {
                $vehicles[] = [
                    'id' => $marque->getId() . '-' . $modele->getId(),
                    'marque_id' => $marque->getId(),
                    'modele_id' => $modele->getId(),
                    'name' => $marque->getLibelle() . ' ' . $modele->getLibelle()
                ];
            }
        }
        
        return new JsonResponse($vehicles);
    }
}
