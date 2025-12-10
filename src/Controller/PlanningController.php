<?php

namespace App\Controller;

use DateTime;
use App\Service\DateHelper;
use App\Service\ReservationHelper;
use App\Service\VehicleAvailabilityService;
use App\Repository\VehiculeRepository;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PlanningController extends AbstractController
{

    private $reservationRepo;
    private $dateTimestamp;
    private $vehiculeRepo;
    private $dateHelper;
    private $reservationHelper;
    private $vehicleAvailabilityService;


    public function __construct(
        ReservationHelper $reservationHelper,
        DateHelper $dateHelper,
        ReservationRepository $reservationRepo,
        VehiculeRepository $vehiculeRepo,
        VehicleAvailabilityService $vehicleAvailabilityService
    ) {

        $this->reservationRepo = $reservationRepo;
        $this->vehiculeRepo = $vehiculeRepo;
        $this->dateHelper = $dateHelper;
        $this->reservationHelper = $reservationHelper;
        $this->vehicleAvailabilityService = $vehicleAvailabilityService;
    }

    /**
     * @Route("/backoffice/planning-general", name="planGen", methods={"GET","POST"})
     */
    public function planGen(): Response
    {

        return $this->render('admin/planning/planGen.html.twig');
    }

    /**
     * @Route("/backoffice/planning-general-modern", name="planGenModern", methods={"GET","POST"})
     */
    public function planGenModern(): Response
    {
        return $this->render('admin/planning/planGenModern.html.twig');
    }

    /**
     * @Route("/backoffice/planning-general-modern2", name="planGenModern2", methods={"GET","POST"})
     */
    public function planGenModern2(): Response
    {
        return $this->render('admin/planning/planGenModern2.html.twig');
    }


    /**
     * @Route("/planningGeneralData", name="planningGeneralData", methods={"GET","POST"})
     */

    public function planningGeneralData(Request $request, ReservationRepository $reservationRepo, VehiculeRepository $vehiculeRepo, NormalizerInterface $normalizer)
    {

        //toutes les réservations sauf canceled , stopsales et tous
        $reservations = $reservationRepo->findResasPlanGen();
        $vehicules = $vehiculeRepo->findAll();

        //mettre toutes les véhicules reservées dans un tableau
        //à utiliser si on veut seulement afficher les véhicules ayant des réservations
        $vehiculesInvolved = [];
        foreach ($reservations as $res) {
            array_push($vehiculesInvolved, $res->getVehicule());
        }

        //se debarasser des doublons
        $vehiculesInvolved = array_unique($vehiculesInvolved);

        //recuperation date debut et fin de l'ensemble des reservations liées à une voiture
        //afficher tous les véhicules
        // $allVehicules = $this->vehiculeRepo->findAll();

        $allVehiculesWithoutVendu = $this->vehiculeRepo->findAllVehiculesWithoutVendu();

        $data1 = array();
        $datas = array();
        $data2 = [];
        //liste des véhicules pour être affiché sur le planning (colonne à gauche)
        foreach ($allVehiculesWithoutVendu as $key => $vehicule) {
            $i = 0;
            $reservationsV = $reservationRepo->findLastReservationsV($vehicule);
            foreach ($reservationsV as $res) {
                $i++;
            }
            $data1[$key]['id'] = $vehicule->getId();
            $data1[$key]['text'] = $vehicule->getMarque() . " " . $vehicule->getModele() . " " . $vehicule->getImmatriculation();
            $data1[$key]['marque_modele'] = $vehicule->getMarque() . " " . $vehicule->getModele();
            //afficher tous les véhicules
            if (count($vehicule->getReservations()) == 0) {
                $data1[$key]['unscheduled'] = true;
            }
            $data1[$key]['render'] = "split";
            $data1[$key]['parent'] = 0;
        }


        $c = 0;
        //liste des réservations qui vont être affichées dans la colonne de droite
        foreach ($reservations as $key => $reservation) {

            $datas[$key]['id'] = uniqid();
            $datas[$key]['id_r'] = $reservation->getId();
            $datas[$key]['client'] = $reservation->getClient()->getNom() . " " . $reservation->getClient()->getPrenom();
            $datas[$key]['start_date'] = $reservation->getDateDebut()->format('d-m-Y H:i');
            $datas[$key]['start_date_formated'] = $reservation->getDateDebut()->format('d-m-Y H:i');

            //            $datas[$key]['duration'] = $this->dateHelper->calculDuree($reservation->getDateDebut(), $reservation->getDateFin());
            $datas[$key]['duration'] = $this->dateHelper->getDureeMinute($reservation->getDateDebut(), $reservation->getDateFin());
            $datas[$key]['end_date_formated'] = $reservation->getDateFin()->format('d-m-Y H:i');
            $datas[$key]['parent'] = $reservation->getVehicule()->getId();
            $datas[$key]['agenceDepart'] = $reservation->getAgenceDepart();
            $datas[$key]['agenceRetour'] = $reservation->getAgenceRetour();
            $datas[$key]['reference'] = $reservation->getReference();
            $datas[$key]['immatriculation'] = $reservation->getVehicule()->getImmatriculation();
            $datas[$key]['telClient'] = $reservation->getClient()->getTelephone();
            $datas[$key]['tarifResa'] = $reservation->getPrix();
            $datas[$key]['tarifVehicule'] = $reservation->getTarifVehicule();
            $datas[$key]['tarifOptionsGaranties'] = $reservation->getPrixOptions() + $reservation->getPrixGaranties();
            $datas[$key]['vehicule'] = $this->vehiculeObjToArray($reservation);
            // $datas[$key]['unscheduled'] = true;
            $datas[$key]['text'] = ""; //util pour eviter erreur quick_info
            // tester si une reservation est en cours ou términé ou nouvelle
            if ($reservation->getDateDebut() < $this->dateHelper->dateNow() && $this->dateHelper->dateNow() < $reservation->getDateFin() && $reservation->getCodeReservation() != 'stopSale') {

                $datas[$key]['etat'] = 'encours';
            }
            if ($reservation->getDateFin() > $this->dateHelper->dateNow() && $reservation->getDateFin() > $this->dateHelper->dateNow() && $reservation->getCodeReservation() != 'stopSale') {

                $datas[$key]['etat'] = 'nouvelle';
            }

            if ($reservation->getDateDebut() < $this->dateHelper->dateNow() && $reservation->getDateFin() < $this->dateHelper->dateNow() && $reservation->getCodeReservation() != 'stopSale') {

                $datas[$key]['etat'] = 'termine';
            }
            if ($reservation->getCodeReservation() == 'stopSale') {

                $datas[$key]['etat'] = 'stopSale';
            }
            //definition couleur tâche en fonction point de départ et point de retour
            if ($reservation->getAgenceDepart() == "garage") {
                $datas[$key]['color'] = "#000000";
            } else if (explode(" ", $reservation->getAgenceDepart())[0] == "Aéroport") {
                $datas[$key]['color'] = "#A9A9A9";
            } else if (explode(" ", $reservation->getAgenceDepart())[0] == "Gare") {
                $datas[$key]['color'] = "#FFC0CB";
            } else if (explode(" ", $reservation->getAgenceDepart())[0] == "Agence") {
                $datas[$key]['color'] = "#ff0000";
            } else {
                $datas[$key]['color'] = "#0d00ff";
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

    public function vehiculeObjToArray($reservation)
    {
        $vehicule = $reservation->getVehicule();
        $vehiculeArray = array();
        $vehiculeArray['id'] = $vehicule->getId();
        $vehiculeArray['marque'] = $vehicule->getMarque()->getLibelle();
        $vehiculeArray['modele'] = $vehicule->getModele()->getLibelle();
        $vehiculeArray['immatriculation'] = $vehicule->getImmatriculation();
        $vehiculeArray['type'] = $vehicule->getType();
        $vehiculeArray['tarif'] = $reservation->getTarifVehicule();

        return $vehiculeArray;
    }


    /**
     * @Route("/backoffice/planning-journalier", name="planJour",methods={"GET","POST"})
     */
    public function planJour(Request $request): Response
    {

        //valeur par défaut de date
        $defaultDate = $this->dateHelper->dateNow();
        $reservations = $this->reservationRepo->findPlanningJournaliers($this->dateHelper->dateNow());

        //lorsque la date est changée par l'utilisateur, on modifie la date de recherche 
        $dateInput = $request->request->get('inputDate');
        if ($dateInput) {
            $dateInput = new DateTime($request->request->get('inputDate'));
            $reservations = $this->reservationRepo->findPlanningJournaliers($dateInput);
            $dateInput = new DateTime($request->request->get('inputDate'));
            $date = $dateInput;
        } else {

            $date = $defaultDate;
        }

        return $this->render('admin/planning/planJour.html.twig', [
            'reservations' => $reservations,
            'date' => $date,

        ]);
        return $this->render('admin/planning/planJour.html.twig');
    }


    /**
     * @Route("/planningJournalierData", name="planningJournalierData", methods={"GET","POST"})
     */
    public function planningJournalierData(Request $request, ReservationRepository $reservationRepo)
    {
        $date = $request->query->get('date');

        //creation d'une date valide en php à partir d'une date de javascript.
        $date1 = \DateTime::createFromFormat('D M d Y H:i:s e+', $date);
        $reservations = $reservationRepo->findPlanningJournaliers($date1);

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
     * @Route("/backoffice/vehicules-disponibles", name="vehiculeDispo", methods={"GET","POST"})
     */
    public function vehiculeDispo(Request $request): Response
    {
        //valeur par défaut de date
        $defaultDate = $this->dateHelper->dateNow();
        $reservations = $this->reservationRepo->findReservationAndStopSalesIncludeDate($this->dateHelper->dateNow());

        //lorsque la date est changée par l'utilisateur, on modifie la date de recherche 
        $dateInput = $request->request->get('inputDate');
        if ($dateInput) {
            $dateInput = new DateTime($request->request->get('inputDate'));
            $reservations = $this->reservationRepo->findReservationAndStopSalesIncludeDate($dateInput);
            $dateInput = new DateTime($request->request->get('inputDate'));
            $date = $dateInput;
        } else {

            $date = $defaultDate;
        }
        //tableau contenant  les véhicules disponilbes - using new service that considers stop sales
        // Create date range: from date to date + 1 day (for single date check)
        $dateEnd = clone $date;
        $dateEnd->modify('+1 day');
        $vehiculesDisponible = $this->vehicleAvailabilityService->getAvailableVehicles($date, $dateEnd);

        // tableau contenant listes des reservations passé des véhicules dispobibles 
        $listPastReservations = $this->reservationHelper->getPastReservations($vehiculesDisponible, $date);
        // tableau contenant listes des reservations futur des véhicules dispobibles 
        $listNextReservations = $this->reservationHelper->getNextReservations($vehiculesDisponible, $date);

        return $this->render('admin/planning/vehicule_dispo.html.twig', [
            'vehiculesDisponible' => $vehiculesDisponible,
            'date' => $date,
            'listPastReservations' => $listPastReservations,
            'listNextReservations' => $listNextReservations,
        ]);
    }
}
