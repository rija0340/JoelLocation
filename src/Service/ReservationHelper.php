<?php

namespace App\Service;

use App\Repository\TarifsRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\ReservationRepository;
use App\Repository\VehiculeRepository;


class ReservationHelper
{

    private $vehiculeRepo;
    private $optionsRepo;
    private $garantiesRepo;
    private $tarifsRepo;
    private $dateHelper;
    private $reservationRepo;

    public function __construct(ReservationRepository $reservationRepo, DateHelper $dateHelper, TarifsRepository $tarifsRepo, VehiculeRepository $vehiculeRepo, OptionsRepository $optionsRepo, GarantieRepository $garantiesRepo)
    {

        $this->vehiculeRepo = $vehiculeRepo;
        $this->optionsRepo = $optionsRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->tarifsRepo = $tarifsRepo;
        $this->dateHelper = $dateHelper;
        $this->reservationRepo = $reservationRepo;
    }

    //paramètres : reservations qui sont inclus durant l'intervalle de date de début et date de fin 
    //cette fonction renvoi les véhicules disponibles qui ne sont pas occupées dans ces réservations
    public function getVehiculesDisponible($reservations)
    {

        $vehicules = $this->vehiculeRepo->findAll();
        //mettre toutes les véhicules reservées dans un tableau
        $vehiculesInvolved = [];
        foreach ($reservations as $res) {
            array_push($vehiculesInvolved, $res->getVehicule());
        }

        $vehiculesInvolved = array_unique($vehiculesInvolved);

        //detecter les véhicules reservé et retenir les autres qui sont disponible dans l'array $vehiculesDispobible
        $vehiculesDisponible = [];
        foreach ($vehicules as $veh) {
            if (in_array($veh, $vehiculesInvolved)) {
            } else {
                array_push($vehiculesDisponible, $veh);
            }
        }
        return $vehiculesDisponible;
    }

    /**
     * @return array of associatives array
     */
    public function getPastReservations($vehiculesDisponible, $date)
    {
        //last reservations return an array and compare vehicules in 
        $pastReservations = [];
        $data = [];
        $listPastReservations = [];
        // boucler les vehicules dispobibles et prendres les reservations pour chaque véhicule
        foreach ($vehiculesDisponible as $vehicule) {
            $pastReservations = $this->reservationRepo->findLastReservations($vehicule, $date);
            if ($pastReservations != null) {
                $datesFin = [];
                foreach ($pastReservations as  $res) {
                    array_push($datesFin, $res->getDateFin());
                }
                $dateRetour = max($datesFin);
                array_push($listPastReservations, $this->reservationRepo->findOneBy(['vehicule' => $vehicule, 'date_fin' => $dateRetour]));
            }
        }
        return $listPastReservations;
    }

    /**
     * @return array of associatives array
     */
    public function getNextReservations($vehiculesDisponible, $date)
    {
        //last reservations return an array and compare vehicules in 
        $nextReservations = [];
        $data = [];
        $listNextReservations = [];
        foreach ($vehiculesDisponible as $vehicule) {

            $nextReservations = $this->reservationRepo->findNextReservations($vehicule, $date);

            if ($nextReservations != null) {
                $datesDepart = [];
                foreach ($nextReservations as  $res) {
                    array_push($datesDepart, $res->getDateDebut());
                }
                $datesDepart = min($datesDepart);
                array_push($listNextReservations, $this->reservationRepo->findBy(['vehicule' => $vehicule, 'date_debut' => $datesDepart]));
            }
        }

        return $listNextReservations;
    }
}
