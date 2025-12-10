<?php

namespace App\Controller\Testing;

use App\Service\VehicleAvailabilityService;
use App\Repository\VehiculeRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Controller to test vehicle availability logic
 * @Route("/testing/availability")
 * @IsGranted("ROLE_SUPER_ADMIN")
 */
class AvailabilityTestingController extends AbstractController
{
    private $vehicleAvailabilityService;
    private $vehiculeRepo;
    private $reservationRepo;

    public function __construct(
        VehicleAvailabilityService $vehicleAvailabilityService,
        VehiculeRepository $vehiculeRepo,
        ReservationRepository $reservationRepo
    ) {
        $this->vehicleAvailabilityService = $vehicleAvailabilityService;
        $this->vehiculeRepo = $vehiculeRepo;
        $this->reservationRepo = $reservationRepo;
    }

    /**
     * @Route("/", name="availability_testing_index")
     */
    public function index(): Response
    {
        $vehicles = $this->vehiculeRepo->findAllVehiculesWithoutVendu();

        return $this->render('testing/availability/index.html.twig', [
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * @Route("/check", name="availability_testing_check", methods={"POST"})
     */
    public function checkAvailability(Request $request): Response
    {
        $dateDebutStr = $request->request->get('date_debut');
        $dateFinStr = $request->request->get('date_fin');
        $vehicleId = $request->request->get('vehicle_id');

        if (!$dateDebutStr || !$dateFinStr) {
            $this->addFlash('error', 'Veuillez renseigner les dates de début et de fin.');
            return $this->redirectToRoute('availability_testing_index');
        }

        try {
            $dateDebut = new \DateTime($dateDebutStr);
            $dateFin = new \DateTime($dateFinStr);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Format de date invalide.');
            return $this->redirectToRoute('availability_testing_index');
        }

        if ($dateDebut >= $dateFin) {
            $this->addFlash('error', 'La date de début doit être antérieure à la date de fin.');
            return $this->redirectToRoute('availability_testing_index');
        }

        // Get detailed availability
        $detailedAvailability = $this->vehicleAvailabilityService->getDetailedAvailability($dateDebut, $dateFin);

        // If a specific vehicle was selected, get its detailed info
        $vehicleDetail = null;
        if ($vehicleId) {
            $vehicle = $this->vehiculeRepo->find($vehicleId);
            if ($vehicle) {
                $vehicleDetail = $this->vehicleAvailabilityService->checkVehicleAvailabilityDetailed($vehicle, $dateDebut, $dateFin);
            }
        }

        $vehicles = $this->vehiculeRepo->findAllVehiculesWithoutVendu();

        return $this->render('testing/availability/index.html.twig', [
            'vehicles' => $vehicles,
            'results' => $detailedAvailability,
            'vehicle_detail' => $vehicleDetail,
            'date_debut' => $dateDebutStr,
            'date_fin' => $dateFinStr,
            'selected_vehicle_id' => $vehicleId,
        ]);
    }

    /**
     * @Route("/api/check", name="availability_testing_api_check", methods={"GET"})
     */
    public function apiCheckAvailability(Request $request): JsonResponse
    {
        $dateDebutStr = $request->query->get('date_debut');
        $dateFinStr = $request->query->get('date_fin');
        $vehicleId = $request->query->get('vehicle_id');

        if (!$dateDebutStr || !$dateFinStr) {
            return new JsonResponse(['error' => 'Missing date_debut or date_fin parameters'], 400);
        }

        try {
            $dateDebut = new \DateTime($dateDebutStr);
            $dateFin = new \DateTime($dateFinStr);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Invalid date format'], 400);
        }

        if ($vehicleId) {
            $vehicle = $this->vehiculeRepo->find($vehicleId);
            if (!$vehicle) {
                return new JsonResponse(['error' => 'Vehicle not found'], 404);
            }
            $result = $this->vehicleAvailabilityService->checkVehicleAvailabilityDetailed($vehicle, $dateDebut, $dateFin);
        } else {
            $result = $this->vehicleAvailabilityService->getDetailedAvailability($dateDebut, $dateFin);
        }

        // Convert DateTime objects to strings for JSON
        $result = $this->convertDateTimesToStrings($result);

        return new JsonResponse($result);
    }

    /**
     * @Route("/blocking-entries", name="availability_testing_blocking_entries", methods={"GET"})
     */
    public function viewBlockingEntries(Request $request): Response
    {
        $dateDebutStr = $request->query->get('date_debut', (new \DateTime())->format('Y-m-d H:i'));
        $dateFinStr = $request->query->get('date_fin', (new \DateTime('+7 days'))->format('Y-m-d H:i'));

        try {
            $dateDebut = new \DateTime($dateDebutStr);
            $dateFin = new \DateTime($dateFinStr);
        } catch (\Exception $e) {
            $dateDebut = new \DateTime();
            $dateFin = new \DateTime('+7 days');
        }

        $blockingEntries = $this->reservationRepo->findAllBlockingEntriesForDates($dateDebut, $dateFin);
        $stopSales = $this->reservationRepo->findStopSalesForDates($dateDebut, $dateFin);
        $reservationsOnly = $this->reservationRepo->findReservationsOnlyForDates($dateDebut, $dateFin);

        return $this->render('testing/availability/blocking_entries.html.twig', [
            'blocking_entries' => $blockingEntries,
            'stop_sales' => $stopSales,
            'reservations_only' => $reservationsOnly,
            'date_debut' => $dateDebutStr,
            'date_fin' => $dateFinStr,
        ]);
    }

    /**
     * Recursively convert DateTime objects to strings
     */
    private function convertDateTimesToStrings($data): array
    {
        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if ($value instanceof \DateTime) {
                $data[$key] = $value->format('Y-m-d H:i:s');
            } elseif (is_array($value)) {
                $data[$key] = $this->convertDateTimesToStrings($value);
            }
        }

        return $data;
    }
}
