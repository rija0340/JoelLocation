<?php

namespace App\Service;

use App\Entity\Vehicule;
use App\Repository\ReservationRepository;
use App\Repository\VehiculeRepository;

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
     * Check if a vehicle is available during a specific date range
     */
    public function isVehicleAvailable(Vehicule $vehicle, \DateTime $dateDebut, \DateTime $dateFin): bool
    {
        // Check if there are any reservations that overlap with the requested dates
        $overlappingReservations = $this->reservationRepo->findReservationIncludeDates($dateDebut, $dateFin);
        
        foreach ($overlappingReservations as $reservation) {
            if ($reservation->getVehicule() === $vehicle) {
                return false; // Vehicle is already booked during this period
            }
        }

        return true; // Vehicle is available
    }

    /**
     * Get available vehicles for a specific date range
     */
    public function getAvailableVehicles(\DateTime $dateDebut, \DateTime $dateFin): array
    {
        // Get all vehicles
        $allVehicles = $this->vehiculeRepo->findAllVehiculesWithoutVendu();

        // Find reservations that overlap with the date range
        $overlappingReservations = $this->reservationRepo->findReservationIncludeDates($dateDebut, $dateFin);
        
        // Get vehicles that are not available
        $unavailableVehicles = [];
        foreach ($overlappingReservations as $reservation) {
            $unavailableVehicles[] = $reservation->getVehicule();
        }

        // Filter out unavailable vehicles
        $availableVehicles = [];
        foreach ($allVehicles as $vehicle) {
            $isAvailable = true;
            foreach ($unavailableVehicles as $unavailableVehicle) {
                if ($vehicle === $unavailableVehicle) {
                    $isAvailable = false;
                    break;
                }
            }
            
            if ($isAvailable) {
                $availableVehicles[] = $vehicle;
            }
        }

        return $availableVehicles;
    }

    /**
     * Check if a specific vehicle is involved in any reservations during the given dates
     */
    public function isVehicleInvolvedInReservations(Vehicule $vehicle, \DateTime $dateDebut, \DateTime $dateFin): bool
    {
        $reservations = $this->reservationRepo->findReservationIncludeDates($dateDebut, $dateFin);
        
        foreach ($reservations as $reservation) {
            if ($reservation->getVehicule() === $vehicle) {
                return true;
            }
        }

        return false;
    }
}