<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Form\RechercheAVType;
use App\Service\TarifsHelper;
use App\Repository\UserRepository;
use App\Repository\TarifsRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;
use App\Repository\ReservationRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RechercheController extends AbstractController
{

    private $userRepo;
    private $reservationRepo;
    private $dateTimestamp;
    private $vehiculeRepo;
    private $optionsRepo;
    private $garantiesRepo;
    private $tarifsRepo;
    private $dateHelper;
    private $tarifsHelper;

    public function __construct(TarifsHelper $tarifsHelper, DateHelper $dateHelper, TarifsRepository $tarifsRepo, ReservationRepository $reservationRepo,  UserRepository $userRepo, VehiculeRepository $vehiculeRepo, OptionsRepository $optionsRepo, GarantieRepository $garantiesRepo)
    {

        $this->reservationRepo = $reservationRepo;
        $this->vehiculeRepo = $vehiculeRepo;
        $this->optionsRepo = $optionsRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->userRepo = $userRepo;
        $this->tarifsRepo = $tarifsRepo;
        $this->dateHelper = $dateHelper;
        $this->tarifsHelper = $tarifsHelper;
    }
    /**
     * @Route("/recherchesimple", name="recherche_simple", methods={"GET", "POST"})
     */
    public function rechercheSimple(Request $request, UserRepository $userRepository, ReservationRepository $reservationRepository): Response
    {
        $recherche = $request->query->get('recherche');

        $reservation[] = new Reservation();

        if ($recherche != null) {

            $client_nom = $recherche;

            $reservation[] = new Reservation();

            $client = $userRepository->findOneBy(["nom" => $client_nom]);

            if ($client != null) {
                $reservation = $reservationRepository->findBy(["client" => $client]);
            } else {
                $reservation = $reservationRepository->findBy(["reference" => $recherche]);
            }
            $datas = array();

            foreach ($reservation as $key => $res) {
                if ($res->getDateFin() < new \Datetime('now')) {
                    $datas[$key]['status'] = 0; // reservaton terminée
                } else {
                    $datas[$key]['status'] = 1; //réservation en cours
                }
                $datas[$key]['id'] = $res->getId();
                $datas[$key]['prix'] = $res->getPrix();
                $datas[$key]['dateDepart'] = $res->getDateDebut()->format('d-m-Y H:i');
                $datas[$key]['dateRetour'] = $res->getDateFin()->format('d-m-Y H:i');
                $datas[$key]['dateResa'] = $res->getDateReservation()->format('d-m-Y H:i');
                $datas[$key]['nomPrenomClient'] = $res->getClient()->getNom() . " " . $res->getClient()->getPrenom();
                $datas[$key]['mailClient'] = $res->getClient()->getMail();
                $datas[$key]['dureeResa'] = $res->getDuree();
                $datas[$key]['codeResa'] = $res->getCodeReservation();
                $datas[$key]['vehicule'] = $res->getVehicule()->getMarque()->getLibelle() . " " . $res->getVehicule()->getModele() . " " . $res->getVehicule()->getImmatriculation();
            }

            return new JsonResponse($datas);
        }
        return $this->redirectToRoute('reservation_index');
    }

    /**
     * @Route("/rechercheimmatriculation", name="recherche_immatriculation", methods={"GET", "POST"})
     */
    public function rechercheImmatriculation(Request $request): Response
    {

        $idVehicule = $request->query->get('idVehicule');
        $date = $request->query->get('date');

        if ($idVehicule != null && $date != null) {
            $date = new \DateTime($date);
            $vehicule = $this->vehiculeRepo->find($idVehicule);

            $reservations = new Reservation();
            $reservations = $this->reservationRepo->findRechercheIM($vehicule, $date);

            $datas = array();

            foreach ($reservations as $key => $res) {
                if ($res->getDateFin() < new \Datetime('now')) {
                    $datas[$key]['status'] = 0; //terminé
                } else {
                    $datas[$key]['status'] = 1; //en cours
                }
                $datas[$key]['id'] = $res->getId();
                $datas[$key]['prix'] = $res->getPrix();
                $datas[$key]['dateDepart'] = $res->getDateDebut()->format('d-m-Y H:i');
                $datas[$key]['dateRetour'] = $res->getDateFin()->format('d-m-Y H:i');
                $datas[$key]['dateResa'] = $res->getDateReservation()->format('d-m-Y H:i');
                $datas[$key]['nomPrenomClient'] = $res->getClient()->getNom() . " " . $res->getClient()->getPrenom();
                $datas[$key]['mailClient'] = $res->getClient()->getMail();
                $datas[$key]['dureeResa'] = $res->getDuree();
                $datas[$key]['codeResa'] = $res->getCodeReservation();
                $datas[$key]['vehicule'] = $res->getVehicule()->getMarque()->getLibelle() . " " . $res->getVehicule()->getModele() . " " . $res->getVehicule()->getImmatriculation();
            }

            return new JsonResponse($datas);
        }
        return $this->redirectToRoute('reservation_index');
    }
    //traitement pour recherche simple, recherche par immatriculation et recherche avancée
    /**
     * @Route("/rechercher_res", name="rechercher_res", methods ={"GET","POST"})
     */
    public function rechercher_res(Request $request): Response
    {


        $vehicules = $this->vehiculeRepo->findAll();
        $formRA = $this->createForm(RechercheAVType::class);

        $dateNow = $this->dateHelper->dateNow();

        //tous les resultats null par défaut
        $resultatRS = null;
        $resultatRIM = null;

        $formRA->handleRequest($request);
        //traiement pour recherche simple
        if ($request->request->get("inputRechercheSimple")) {
            $resultatRS = $this->getRechercheSimple($request->request->get("inputRechercheSimple"));
        }

        //traitement pour recherche Immatriculation
        $dateRechercheRIM = null;
        $vehiculeRechercheRIM = null;
        if ($request->request->get("inputVehicule_RIM") && $request->request->get("inputDate_RIM")) {
            $resultatRIM = $this->getRechercheImmatriculation($request->request->get("inputVehicule_RIM"), $request->request->get("inputDate_RIM"));
            $dateRechercheRIM =  new DateTime($request->request->get("inputDate_RIM"));
            $vehiculeRechercheRIM = $this->vehiculeRepo->find($request->request->get("inputVehicule_RIM"));
        }

        // dd($resultatRIM);

        //traitement pour recherche avancé
        if ($formRA->isSubmitted() && $formRA->isValid()) {

            $recherche_av = $request->request->get('recherche_av');
            $typeDate = $recherche_av["typeDate"];
            $debutPeriode = new \DateTime($recherche_av["debutPeriode"]);
            $finPeriode = new \DateTime($recherche_av["finPeriode"]);
            $categorie = $recherche_av["categorie"];
            $typeTarif = $recherche_av["typeTarif"];
            $codePromo = $recherche_av["codePromo"];

            switch ($typeDate) {
                case 'dateReservation':
                    $reservations =  $this->reservationRepo->findDateResIncludedBetwn($debutPeriode, $finPeriode,  $categorie, $typeTarif);
                    break;
                case 'dateDepart':
                    $reservations =  $this->reservationRepo->findDateDepartIncludedBetwn($debutPeriode, $finPeriode,  $categorie, $typeTarif);
                    break;
                case 'dateRetour':
                    $reservations =  $this->reservationRepo->findDateRetourIncludedBetwn($debutPeriode, $finPeriode,  $categorie, $typeTarif);
                    break;
            }

            //compte nombre de jours total reservation
            $dureeTotal = 0;
            foreach ($reservations as $reserv) {
                $dureeTotal = $reserv->getDuree() + $dureeTotal;
            }
            //calcul chiffre d'affaire
            $chiffreAffaire = 0;
            foreach ($reservations as $reserv) {
                $chiffreAffaire = $reserv->getPrix() + $chiffreAffaire;
            }
            return $this->render('admin/reservation/recherche/resultat_RA.html.twig', [
                'dureeTotal' => $dureeTotal,
                'chiffreAffaire' => $chiffreAffaire,
                'nbreservations' => count($reservations),
                'reservations' => $reservations,
                'debutPeriode' =>  $debutPeriode,
                'finPeriode' => $finPeriode
            ]);
        }

        return $this->render('admin/reservation/recherche/rechercher_res.html.twig', [
            'vehicules' => $vehicules,
            'formRA' => $formRA->createView(),
            'resultatRS' => $resultatRS,
            'resultatRIM' => $resultatRIM,
            'dateRechercheRIM' => $dateRechercheRIM,
            'vehiculeRechercheRIM' => $vehiculeRechercheRIM,
            'dateNow' => $dateNow

        ]);
    }

    public function getRechercheSimple($recherche)
    {

        // dd($request);
        // die();
        $reservation[] = new Reservation();

        if ($recherche != null) {
            // $client_id = (int)$recherche;
            $client_nom = $recherche;
            // $client = new User();
            $reservation[] = new Reservation();
            //if($client_id){
            // $client = $userRepository->findOneBy(["id" => $client_id]);
            $client = $this->userRepo->findOneBy(["nom" => $client_nom]);
            //}
            // if ($client == null) {
            //     $client = $userRepository->findOneBy(["nom" => $recherche]);
            // }
            if ($client != null) {
                $reservation = $this->reservationRepo->findBy(["client" => $client]);
            } else {
                $reservation = $this->reservationRepo->findBy(["reference" => $recherche]);
            }
        }
        return $reservation;
    }

    public function getRechercheImmatriculation($idVehicule, $date)
    {

        if ($idVehicule != null && $date != null) {
            $date = new \DateTime($date);
            $vehicule = $this->vehiculeRepo->find($idVehicule);

            // dump($date, $vehicule);
            // die();

            $reservations = new Reservation();
            $reservations = $this->reservationRepo->findRechercheIM($vehicule, $date);
        }
        return $reservations;
    }
}
