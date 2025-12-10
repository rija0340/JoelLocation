<?php

namespace App\Service;

use App\Entity\Vehicule;
use App\Repository\ReservationRepository;
use App\Repository\VehiculeRepository;

/**
 * Service for checking and managing vehicle availability.
 * 
 * This service properly handles both reservations AND stop sales when determining
 * vehicle availability for a given date range.
 */
class VehicleAvailabilityService
{
    private $reservationRepo;
    private $vehiculeRepo;

    public function __construct(
        ReservationRepository $reservationRepo,
        VehiculeRepository $vehiculeRepo
    ) {
        $this->reservationRepo = $reservationRepo;
        $this->vehiculeRepo = $vehiculeRepo;
    }

    /**
     * Check if a vehicle is available during a specific date range.
     * This method considers BOTH reservations AND stop sales.
     * 
     * @param Vehicule $vehicle The vehicle to check
     * @param \DateTime $dateDebut Start date
     * @param \DateTime $dateFin End date
     * @return bool True if available, false otherwise
     */
    public function isVehicleAvailable(Vehicule $vehicle, \DateTime $dateDebut, \DateTime $dateFin): bool
    {
        $blockingEntries = $this->reservationRepo->findAllBlockingEntriesForDates($dateDebut, $dateFin, $vehicle);
        return count($blockingEntries) === 0;
    }

    /**
     * Get available vehicles for a specific date range.
     * This method considers BOTH reservations AND stop sales.
     * 
     * @param \DateTime $dateDebut Start date
     * @param \DateTime $dateFin End date
     * @return array Array of available Vehicule entities
     */
    public function getAvailableVehicles(\DateTime $dateDebut, \DateTime $dateFin): array
    {
        // Get all vehicles (excluding sold ones)
        $allVehicles = $this->vehiculeRepo->findAllVehiculesWithoutVendu();

        // Find ALL blocking entries (reservations + stop sales) for the date range
        $blockingEntries = $this->reservationRepo->findAllBlockingEntriesForDates($dateDebut, $dateFin);

        // Build a list of unavailable vehicle IDs
        $unavailableVehicleIds = [];
        foreach ($blockingEntries as $entry) {
            if ($entry->getVehicule() !== null) {
                $unavailableVehicleIds[$entry->getVehicule()->getId()] = true;
            }
        }

        // Filter out unavailable vehicles
        $availableVehicles = [];
        foreach ($allVehicles as $vehicle) {
            if (!isset($unavailableVehicleIds[$vehicle->getId()])) {
                $availableVehicles[] = $vehicle;
            }
        }

        return $availableVehicles;
    }

    /**
     * Check if a specific vehicle is involved in any reservations during the given dates.
     * Note: This method considers BOTH reservations AND stop sales.
     * 
     * @param Vehicule $vehicle The vehicle to check
     * @param \DateTime $dateDebut Start date
     * @param \DateTime $dateFin End date
     * @return bool True if vehicle is blocked, false otherwise
     */
    public function isVehicleInvolvedInReservations(Vehicule $vehicle, \DateTime $dateDebut, \DateTime $dateFin): bool
    {
        return !$this->isVehicleAvailable($vehicle, $dateDebut, $dateFin);
    }

    /**
     * Get detailed availability information for all vehicles in a date range.
     * Useful for testing and debugging.
     * 
     * @param \DateTime $dateDebut Start date
     * @param \DateTime $dateFin End date
     * @return array Detailed availability data
     */
    public function getDetailedAvailability(\DateTime $dateDebut, \DateTime $dateFin): array
    {
        $allVehicles = $this->vehiculeRepo->findAllVehiculesWithoutVendu();
        $blockingEntries = $this->reservationRepo->findAllBlockingEntriesForDates($dateDebut, $dateFin);
        $stopSalesOnly = $this->reservationRepo->findStopSalesForDates($dateDebut, $dateFin);
        $reservationsOnly = $this->reservationRepo->findReservationsOnlyForDates($dateDebut, $dateFin);

        // Build blocking info per vehicle
        $blockingByVehicle = [];
        foreach ($blockingEntries as $entry) {
            if ($entry->getVehicule() !== null) {
                $vehicleId = $entry->getVehicule()->getId();
                if (!isset($blockingByVehicle[$vehicleId])) {
                    $blockingByVehicle[$vehicleId] = [];
                }
                $blockingByVehicle[$vehicleId][] = [
                    'id' => $entry->getId(),
                    'type' => $entry->getCodeReservation() === 'stopSale' ? 'stop_sale' : 'reservation',
                    'reference' => $entry->getReference(),
                    'date_debut' => $entry->getDateDebut(),
                    'date_fin' => $entry->getDateFin(),
                    'client' => $entry->getClient() ? $entry->getClient()->getNom() . ' ' . $entry->getClient()->getPrenom() : 'N/A',
                    'commentaire' => $entry->getCommentaire(),
                ];
            }
        }

        // Build a set of vehicle IDs from the all vehicles list (for filtering)
        $allVehicleIds = [];
        foreach ($allVehicles as $vehicle) {
            $allVehicleIds[$vehicle->getId()] = true;
        }

        // Count unavailable vehicles that are actually in the allVehicles list (exclude sold vehicles)
        $unavailableCount = 0;
        foreach ($blockingByVehicle as $vehicleId => $entries) {
            if (isset($allVehicleIds[$vehicleId])) {
                $unavailableCount++;
            }
        }

        // Build availability info for each vehicle
        $vehicleAvailability = [];
        foreach ($allVehicles as $vehicle) {
            $vehicleId = $vehicle->getId();
            $isAvailable = !isset($blockingByVehicle[$vehicleId]);

            $vehicleAvailability[] = [
                'id' => $vehicleId,
                'immatriculation' => $vehicle->getImmatriculation(),
                'marque' => $vehicle->getMarque() ? $vehicle->getMarque()->getLibelle() : 'N/A',
                'modele' => $vehicle->getModele() ? $vehicle->getModele()->getLibelle() : 'N/A',
                'is_available' => $isAvailable,
                'blocking_entries' => $blockingByVehicle[$vehicleId] ?? [],
            ];
        }

        // Sort: unavailable first, then available
        usort($vehicleAvailability, function ($a, $b) {
            return $a['is_available'] <=> $b['is_available'];
        });

        return [
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'total_vehicles' => count($allVehicles),
            'available_count' => count($allVehicles) - $unavailableCount,
            'unavailable_count' => $unavailableCount,
            'total_blocking_entries' => count($blockingEntries),
            'reservations_count' => count($reservationsOnly),
            'stop_sales_count' => count($stopSalesOnly),
            'vehicles' => $vehicleAvailability,
        ];
    }

    /**
     * Check availability for a specific vehicle with detailed blocking info.
     * 
     * @param Vehicule $vehicle The vehicle to check
     * @param \DateTime $dateDebut Start date
     * @param \DateTime $dateFin End date
     * @return array Detailed availability info for the vehicle
     */
    public function checkVehicleAvailabilityDetailed(Vehicule $vehicle, \DateTime $dateDebut, \DateTime $dateFin): array
    {
        $blockingEntries = $this->reservationRepo->findAllBlockingEntriesForDates($dateDebut, $dateFin, $vehicle);

        $blockingInfo = [];
        foreach ($blockingEntries as $entry) {
            $blockingInfo[] = [
                'id' => $entry->getId(),
                'type' => $entry->getCodeReservation() === 'stopSale' ? 'stop_sale' : 'reservation',
                'reference' => $entry->getReference(),
                'date_debut' => $entry->getDateDebut(),
                'date_fin' => $entry->getDateFin(),
                'client' => $entry->getClient() ? $entry->getClient()->getNom() . ' ' . $entry->getClient()->getPrenom() : 'N/A',
                'commentaire' => $entry->getCommentaire(),
            ];
        }

        return [
            'vehicle' => [
                'id' => $vehicle->getId(),
                'immatriculation' => $vehicle->getImmatriculation(),
                'marque' => $vehicle->getMarque() ? $vehicle->getMarque()->getLibelle() : 'N/A',
                'modele' => $vehicle->getModele() ? $vehicle->getModele()->getLibelle() : 'N/A',
            ],
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'is_available' => count($blockingEntries) === 0,
            'blocking_entries' => $blockingInfo,
        ];
    }
}