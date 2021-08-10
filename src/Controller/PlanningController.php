<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Vehicule;
use App\Repository\VehiculeRepository;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use App\Service\DateHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Length;

class PlanningController extends AbstractController
{

    private $reservationRepo;
    private $dateTimestamp;
    private $vehiculeRepo;
    private $dateHelper;


    public function __construct(DateHelper $dateHelper, ReservationRepository $reservationRepo, VehiculeRepository $vehiculeRepo)
    {

        $this->reservationRepo = $reservationRepo;
        $this->vehiculeRepo = $vehiculeRepo;
        $this->dateHelper = $dateHelper;
    }

    /**
     * @Route("/planningGeneralData", name="planningGeneralData", methods={"GET"})
     */

    public function planningGeneralData(Request $request, ReservationRepository $reservationRepo, VehiculeRepository $vehiculeRepo, NormalizerInterface $normalizer)
    {

        $reservations = $reservationRepo->findBy(array(),  array('date_debut' => 'ASC'));
        $vehicules = $vehiculeRepo->findAll();

        $i = 0;
        $vehiculesInvolved = [];

        foreach ($vehicules as $vehicule) {
            foreach ($reservations as $reservation) {
                if ($vehicule == $reservation->getVehicule()) {

                    $i++;
                }
            }
            if ($i != 0) {
                $vehiculesInvolved[] = $vehicule;
            }
            $i = 0;
        }

        //recuperation date debut et fin de l'ensemble des reservations liées à une voiture

        $data1 = array();
        $datas = array();
        $data2 = [];
        $resArray = [];

        foreach ($vehiculesInvolved as $key => $vehicule) {
            $i = 0;
            $reservationsV = $reservationRepo->findLastReservationsV($vehicule);
            foreach ($reservationsV as $res) {
                $i++;
            }
            $data1[$key]['id'] = $vehicule->getId();
            $data1[$key]['text'] = $vehicule->getMarque() . " " . $vehicule->getModele() . " " . $vehicule->getImmatriculation();
            $data1[$key]['marque_modele'] = $vehicule->getMarque() . " " . $vehicule->getModele();
            $data1[$key]['start_date'] =  $reservationsV[0]->getDateFin()->format('d-m-Y H:i');
            // $data1[$key]['type'] =  "project";
            $data1[$key]['render'] =  "split";
            $data1[$key]['parent'] =  0;
            $data1[$key]['end_date'] =   $reservationsV[$i - 1]->getDateFin()->format('d-m-Y H:i');
        }

        $c = 0;
        foreach ($reservations as $key => $reservation) {
            $datas[$key]['id'] =  uniqid();
            $datas[$key]['id_r'] =  $reservation->getId();
            $datas[$key]['client'] = $reservation->getClient()->getNom() . " " .  $reservation->getClient()->getPrenom();
            $datas[$key]['start_date'] = $reservation->getDateDebut()->format('d-m-Y H:i');
            $datas[$key]['start_date_formated'] = $reservation->getDateDebut()->format('d-m-Y H:i');

            if (date("H", $reservation->getDateFin()->getTimestamp()) == 0) {
                $datas[$key]['duration'] = ceil(1 + (($reservation->getDateFin()->getTimestamp() - $reservation->getDateDebut()->getTimestamp()) / 60 / 60 / 24));
            } else {
                $datas[$key]['duration'] = ceil(($reservation->getDateFin()->getTimestamp() - $reservation->getDateDebut()->getTimestamp()) / 60 / 60 / 24);
            }

            $datas[$key]['end_date_formated'] = $reservation->getDateFin()->format('d-m-Y H:i');
            $datas[$key]['parent'] = $reservation->getVehicule()->getId();
            $datas[$key]['agenceDepart'] = $reservation->getAgenceDepart();
            $datas[$key]['agenceRetour'] = $reservation->getAgenceRetour();
            $datas[$key]['reference'] = $reservation->getReference();
            $datas[$key]['telClient'] = $reservation->getClient()->getTelephone();
            $datas[$key]['text'] = ""; //util pour eviter erreur quick_info
            // tester si une reservation est en cours
            if ($reservation->getDateDebut() < $this->dateHelper->dateNow() && $this->dateHelper->dateNow() < $reservation->getDateFin()) {

                $datas[$key]['enCours'] = true;
            }
            if ($reservation->getDateFin() < $this->dateHelper->dateNow()) {

                $datas[$key]['enCours'] = false;
            }

            if ($reservation->getAgenceDepart() == "garage") {
                $datas[$key]['color'] =  "#A9A9A9";
            } else if ($reservation->getAgenceDepart() == "aeroport") {
                $datas[$key]['color'] =  "#000000";
            } else if ($reservation->getAgenceDepart() == "gareMaritime") {
                $datas[$key]['color'] =  "#FFC0CB";
            } else if ($reservation->getAgenceDepart() == "agence") {
                $datas[$key]['color'] =  "#ff0000";
            } else if ($reservation->getAgenceDepart() == "pointLivraison") {
                $datas[$key]['color'] =  "#0d00ff";
            }
        }

        foreach ($data1 as $dt1) {
            array_push($data2, $dt1);
            foreach ($datas as $dts) {

                if ($dt1['id'] == $dts['parent']) {
                    array_push($data2, $dts);
                }
            }
        }

        return new JsonResponse($data2);
    }

    /**
     * @Route("/planningJournalierData", name="planningJournalierData", methods={"GET"})
     */

    public function planningJournalierData(Request $request, ReservationRepository $reservationRepo)
    {
        $date = $request->query->get('date');

        //creation d'une date valide en php à partir d'une date de javascript.

        $dateStarted = \DateTime::createFromFormat('D M d Y H:i:s e+', $date);
        $reservations = $reservationRepo->findPlanningJournaliers($dateStarted);

        $datas = array();
        foreach ($reservations as $key => $reservation) {
            $datas[$key]['identification'] = $reservation->getVehicule()->getMarque() . ' ' . $reservation->getVehicule()->getModele() . ' ' . $reservation->getVehicule()->getImmatriculation();
            $datas[$key]['client'] = $reservation->getClient()->getNom() . ' ' . $reservation->getClient()->getPrenom();
            $datas[$key]['start_date_formated'] = $reservation->getDateDebut()->format('d/m/Y - H\Hi');
            $datas[$key]['end_date_formated'] = $reservation->getDateFin()->format('d/m/Y - H\Hi');
        }

        return new JsonResponse($datas);
    }

    /**
     * @Route("/planning-general", name="planGen", methods={"GET"})
     */
    public function planGen(): Response
    {

        return $this->render('admin/planning/planGen.html.twig');
    }

    /**
     * @Route("/planning-journalier", name="planJour", methods={"GET"})
     */
    public function planJour(): Response
    {

        return $this->render('admin/planning/planJour.html.twig');
    }


    /**
     * @Route("/vehiculeDispoData", name="vehiculeDispoData", methods={"GET"})
     */

    public function vehiculeDispoData(Request $request, VehiculeRepository $vehiculeRepo, ReservationRepository $reservationRepo)
    {

        $date = $request->query->get('date');

        $date = \DateTime::createFromFormat('D M d Y H:i:s e+', $date);

        $datas = array();
        foreach ($this->getVehiculesDispo($date) as $key => $vehicule) {

            $datas[$key]['id'] = $vehicule->getId();
            $datas[$key]['immatriculation'] = $vehicule->getImmatriculation();
            $datas[$key]['modele'] = $vehicule->getModele();
            if ($this->getLastReservation($vehicule, $date) != null) {
                $datas[$key]['lastReservation'] = $this->getLastReservation($vehicule, $date)->getDateFin()->format('d-m-Y H:i');
            } else {
                $datas[$key]['lastReservation'] = "Pas de réservation";
            }
            if ($this->getNextReservation($vehicule, $date) != null) {
                $datas[$key]['nextReservation'] =  $this->getNextReservation($vehicule, $date)->getDateDebut()->format('d-m-Y H:i');
            } else {
                $datas[$key]['nextReservation'] = "Pas de réservation";
            }
        }

        return new JsonResponse($datas);
    }

    public function getVehiculesDispo($date)
    {
        $vehicules = $this->vehiculeRepo->findAll();
        $reservations = $this->reservationRepo->findReservationIncludeDate($date);
        $i = 0;
        $vehiculeDispo = [];

        // code pour vehicule avec reservation , mila manao condition ame tsy misy reservation mihitsy
        foreach ($vehicules as $vehicule) {
            foreach ($reservations as $reservation) {
                if ($vehicule == $reservation->getVehicule()) {
                    $i++;
                }
            }
            if ($i == 0) {
                $vehiculeDispo[] = $vehicule;
            }
            $i = 0;
        }
        return $vehiculeDispo;
    }
    public function getLastReservation($vehicule, $date)
    {
        // recuperer dernière et next reservation véhicule dispo

        $lastReservations = $this->reservationRepo->findLastReservations($vehicule, $date);

        if ($lastReservations != null) {

            $dateTimestamp = $date->getTimestamp();
            $minDiff = 365 * 10 *  24 * 60 * 60 * 1000; //une année en milliseconde

            foreach ($lastReservations as $lastReservation) {

                $lastReservationTimestamp = $lastReservation->getDateFin()->getTimestamp();

                if ($dateTimestamp - $lastReservationTimestamp < $minDiff) {
                    $minDiff =  $dateTimestamp - $lastReservationTimestamp;
                    $theLastReserv = $lastReservation;
                }
            }

            return $theLastReserv;
        } else {
            return null;
        }
    }

    public function getNextReservation($vehicule, $date)
    {

        // recuperer next reservation pour véhicule dispo
        $nextReservations = new Reservation();
        $dateTimestamp = $date->getTimestamp();

        $nextReservations = $this->reservationRepo->findNextReservations($vehicule, $date);
        if ($nextReservations != null) {

            $minDiff = 365 * 10 * 24 * 60 * 60 * 1000; //une année en milliseconde

            foreach ($nextReservations as $nextReservation) {

                $nextReservationTimestamp = $nextReservation->getDateDebut()->getTimestamp();

                // echo $lastReservation->getDateFin()->format('d-m-Y') . '  ' .  $date->format('d-m-Y') . '  ' . $lastReservationTimestamp . '  ' . $dateTimestamp . '</br>';

                if ($nextReservationTimestamp - $dateTimestamp    < $minDiff) {
                    $minDiff =   $nextReservationTimestamp - $dateTimestamp;
                    $theNextReserv = $nextReservation;
                }
            }
            return $theNextReserv;
        } else {
            return null;
        }
    }

    /**
     * @Route("/vehicule-dispo", name="vehiculeDispo", methods={"GET"})
     */
    public function VehiculeDispo(VehiculeRepository $vehiculeRepo, ReservationRepository $reservationRepo): Response
    {

        return $this->render('admin/planning/vehicule_dispo.html.twig');
    }
}
