<?php

namespace App\Controller;

use App\Service\DateHelper;
use App\Service\TarifsHelper;
use App\Repository\MarqueRepository;
use App\Repository\ModeleRepository;
use App\Repository\VehiculeRepository;
use App\Repository\ReservationRepository;
use App\Service\ReservationHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VehiculeDataController extends AbstractController
{


    private $vehiculeRepo;
    private $marqueRepo;
    private $modeleRepo;
    private $reservationRepo;
    private $tarifsHelper;
    private $dateHelper;
    private $reservationHelper;

    public function __construct(
        VehiculeRepository $vehiculeRepo,
        MarqueRepository $marqueRepo,
        ModeleRepository $modeleRepo,
        ReservationRepository $reservationRepo,
        TarifsHelper $tarifsHelper,
        DateHelper $dateHelper,
        ReservationHelper $reservationHelper
    ) {

        $this->vehiculeRepo = $vehiculeRepo;
        $this->marqueRepo = $marqueRepo;
        $this->modeleRepo = $modeleRepo;
        $this->reservationRepo = $reservationRepo;
        $this->tarifsHelper = $tarifsHelper;
        $this->dateHelper = $dateHelper;
        $this->reservationHelper = $reservationHelper;
    }

    /**
     * @Route("reservation/vehiculeDispoFonctionDates", name="vehiculeDispoFonctionDates",methods={"GET","POST"}))
     */
    public function vehiculeDispoFonctionDates(Request $request)
    {

        $dateDepart = $request->query->get('dateDepart');
        $dateRetour = $request->query->get('dateRetour');


        $dateDebut = new \DateTime($dateDepart);
        $dateFin = new \DateTime($dateRetour);


        $datas = array();
        $data = array();
        $listeUnique = [];
        $listeVehiculesDispo = array();
        $reservations = $this->reservationRepo->findReservationIncludeDates($dateDebut, $dateFin);
        $listeVehiculesDispo = $this->reservationHelper->getVehiculesDisponible($reservations);

        foreach ($listeVehiculesDispo as $key => $vehicule) {
            $data[$key]['marque'] = $vehicule->getMarque()->getLibelle();
            $data[$key]['modele'] = $vehicule->getModele()->getLibelle();
        }


        $listeUnique = array_unique($data, SORT_REGULAR);
        // dump($listeUnique);
        // die();

        //data2 => liste véhicule sans immatriculation
        $data2 = array();
        foreach ($listeUnique as $key =>  $v) {

            $marque = $this->marqueRepo->findOneBy(['libelle' => $v['marque']]);
            $modele = $this->modeleRepo->findOneBy(['libelle' => $v['modele']]);

            $vehicule = $this->vehiculeRepo->findOneBy(['marque' => $marque, 'modele' => $modele]);
            $tarif = $this->tarifsHelper->calculTarifVehicule($dateDebut, $dateFin, $vehicule);

            $data2[$key]['id'] = $vehicule->getId();
            $data2[$key]['marque'] = $vehicule->getMarque()->getLibelle();
            $data2[$key]['modele'] = $vehicule->getModele()->getLibelle();
            $data2[$key]['carburation'] = $vehicule->getCarburation();
            $data2[$key]['carburation'] = $vehicule->getCarburation();
            $data2[$key]['immatriculation'] = $vehicule->getImmatriculation();
            $data2[$key]['vitesse'] = $vehicule->getVitesse();
            $data2[$key]['bagages'] = $vehicule->getBagages();
            $data2[$key]['atouts'] = $vehicule->getAtouts();
            $data2[$key]['caution'] = $vehicule->getCaution();
            $data2[$key]['portes'] = $vehicule->getPortes();
            $data2[$key]['passagers'] = $vehicule->getPassagers();
            $data2[$key]['image'] = $vehicule->getImage();
            $data2[$key]['tarif'] = $tarif;
            $data2[$key]['tarifJour'] = $tarif / $this->dateHelper->calculDuree($dateDebut, $dateFin);
        }

        foreach ($listeVehiculesDispo as $key => $vehicule) {
            $datas[$key]['id'] = $vehicule->getId();
            $datas[$key]['marque'] = $vehicule->getMarque()->getLibelle();
            $datas[$key]['modele'] = $vehicule->getModele()->getLibelle();
        }

        return new JsonResponse($data2);
    }


    /**
     * @Route("backoffice/reservation/liste-vehicules-disponibles", name="vehicules_disponibles",methods={"GET","POST"}))
     */
    public function vehiculesDisponibles(Request $request)
    {

        $dateDepart = $request->query->get('dateDepart');
        $dateRetour = $request->query->get('dateRetour');

        $dateDebut = new \DateTime($dateDepart);
        $dateFin = new \DateTime($dateRetour);

        $listeUnique = [];
        $listeVehiculesDispo = [];
        $reservations = $this->reservationRepo->findReservationIncludeDates($dateDebut, $dateFin);
        $listeVehiculesDispo = $this->reservationHelper->getVehiculesDisponible($reservations);

        $data2 = array();
        foreach ($listeVehiculesDispo as $key =>  $vehicule) {

            $data2[$key]['id'] = $vehicule->getId();
            $data2[$key]['marque'] = $vehicule->getMarque()->getLibelle();
            $data2[$key]['modele'] = $vehicule->getModele()->getLibelle();
            $data2[$key]['carburation'] = $vehicule->getCarburation();
            $data2[$key]['carburation'] = $vehicule->getCarburation();
            $data2[$key]['immatriculation'] = $vehicule->getImmatriculation();
            $data2[$key]['vitesse'] = $vehicule->getVitesse();
            $data2[$key]['bagages'] = $vehicule->getBagages();
            $data2[$key]['atouts'] = $vehicule->getAtouts();
            $data2[$key]['caution'] = $vehicule->getCaution();
            $data2[$key]['portes'] = $vehicule->getPortes();
            $data2[$key]['passagers'] = $vehicule->getPassagers();
            $data2[$key]['image'] = $vehicule->getImage();
        }

        return new JsonResponse($data2);
    }
    /**
     * @Route("reservation/listeVehicules", name="listeVehicules",methods={"GET","POST"}))
     */
    public function listeVehicules(Request $request)
    {

        $dateDepart = $request->query->get('dateDepart');
        $dateRetour = $request->query->get('dateRetour');
        $dateDepart = new \DateTime($dateDepart);
        $dateRetour = new \DateTime($dateRetour);
        $datas = array();
        // dump($this->reservationRepo->findReservationIncludeDates($dateDepart, $dateRetour));
        // die();

        foreach ($this->reservationRepo->findReservationIncludeDates($dateDepart, $dateRetour) as $key => $reservation) {
            $datas[$key]['id'] = $reservation->getVehicule()->getId();
            $datas[$key]['marque'] = $reservation->getVehicule()->getMarque()->getLibelle();
            $datas[$key]['modele'] = $reservation->getVehicule()->getModele()->getLibelle();
            $datas[$key]['immatriculation'] = $reservation->getVehicule()->getImmatriculation();
        }

        return new JsonResponse($datas);
    }
}
