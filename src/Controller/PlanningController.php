<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Vehicule;
use App\Repository\VehiculeRepository;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
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


    public function __construct(ReservationRepository $reservationRepo, VehiculeRepository $vehiculeRepo)
    {

        $this->reservationRepo = $reservationRepo;
        $this->vehiculeRepo = $vehiculeRepo;
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


            switch ($c) {
                case 0:
                    $datas[$key]['color'] =  "red";
                    $c++;
                    break;
                case 1:
                    $datas[$key]['color'] =  "green";
                    $c++;
                    break;
                case 2:
                    $datas[$key]['color'] =  "blue";
                    $c++;
                    break;
                case 3:
                    $datas[$key]['color'] =  "red";
                    $c++;
                    break;
                    // default:
                    // echo "Désolé, je n'ai pas de message à afficher pour cette note";
            }

            // $datas[$key]['client_name'] = $reservation->getClient()->getNom() . " " .  $reservation->getClient()->getPrenom();
            // $datas[$key]['start_date_formated'] = $reservation->getDateDebut()->format('d/m/Y');
            // $datas[$key]['end_date_formated'] = $reservation->getDateFin()->format('d/m/Y');
        }

        foreach ($data1 as $dt1) {
            array_push($data2, $dt1);
            foreach ($datas as $dts) {

                if ($dt1['id'] == $dts['parent']) {
                    array_push($data2, $dts);
                }
            }
        }

        // dd($data2);
        return new JsonResponse($data2);
    }

    /**
     * @Route("/planningJournalierData", name="planningJournalierData", methods={"GET"})
     */

    public function planningJournalierData(Request $request, ReservationRepository $reservationRepo)
    {

        $reservations = $reservationRepo->findAll();


        $datas = array();
        foreach ($reservations as $key => $reservation) {
            $datas[$key]['id'] = $reservation->getId();
            $datas[$key]['text'] = $reservation->getType();
            $datas[$key]['start_date_formated'] = $reservation->getDateDebut()->format('d/m/Y');
            $datas[$key]['end_date_formated'] = $reservation->getDateFin()->format('d/m/Y');
            $datas[$key]['start_date'] = $reservation->getDateDebut();
            $datas[$key]['end_date'] = $reservation->getDateFin();
            $datas[$key]['client_name'] = $reservation->getClient()->getNom() . " " .  $reservation->getClient()->getPrenom();
            $datas[$key]['color'] = "red";
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
        // avy any amin'ny request ity (AJAX)
        $day = $request->query->get('day');
        $month = $request->query->get('month');
        $year = $request->query->get('year');
        $hours = $request->query->get('hours');
        $minutes = $request->query->get('minutes');

        // $time = strtotime($dateInputAjax);
        $time = date("Y-m-d H:i", mktime($hours, $minutes, 00, $month, $day, $year));
        // $time =  mktime($hours, $minutes, 00, $month, $day, $year);


        //convert a string to date php
        $date = new \DateTime($time);


        // echo $this->getNextReservation($this->getVehiculesDispo($date)[0], $date)->getDateDebut()->format('d-m-Y');
        // echo $this->getLastReservation($this->getVehiculesDispo($date)[0], $date)->getDateFin()->format('d-m-Y');

        //ajout de données dans array puis envoyer vers AJAX
        $datas = array();
        foreach ($this->getVehiculesDispo($date) as $key => $vehicule) {

            $datas[$key]['id'] = $vehicule->getId();
            $datas[$key]['immatriculation'] = $vehicule->getImmatriculation();
            $datas[$key]['modele'] = $vehicule->getModele();
            if ($this->getLastReservation($vehicule, $date) != null) {
                $datas[$key]['lastReservation'] = $this->getLastReservation($vehicule, $date)->getDateFin()->format('d-m-Y H:i');

                // dd($this->getLastReservation($vehicule, $date)->getDateFin()->format('d-m-Y H:i'));
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
        // dd($reservations);

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
