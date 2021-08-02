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
            $client = $userRepository->findOneBy(["nom" => $client_nom]);
            //}
            // if ($client == null) {
            //     $client = $userRepository->findOneBy(["nom" => $recherche]);
            // }
            if ($client != null) {
                $reservation = $reservationRepository->findBy(["client" => $client]);
            } else {
                $reservation = $reservationRepository->findBy(["reference" => $recherche]);
            }
            $datas = array();

            foreach ($reservation as $key => $res) {
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

            // dd($datas);
            // die();

            return new JsonResponse($datas);
        }
        return $this->redirectToRoute('reservation_index');
    }

    /**
     * @Route("/rechercheimmatriculation", name="recherche_immatriculation", methods={"GET", "POST"})
     */
    public function rechercheImmatriculation(Request $request): Response
    {

        // dump($request);
        // die();

        $idVehicule = $request->query->get('idVehicule');
        $date = $request->query->get('date');

        if ($idVehicule != null && $date != null) {
            $date = new \DateTime($date);
            $vehicule = $this->vehiculeRepo->find($idVehicule);

            // dump($date, $vehicule);
            // die();

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

    /**
     * @Route("/rechercher_res", name="rechercher_res")
     */
    public function rechercher_res(Request $request): Response
    {
        $vehicules = $this->vehiculeRepo->findAll();
        $form = $this->createForm(RechercheAVType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

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

            //compte nombre de jours total resevation
            $dureeTotal = 0;
            foreach ($reservations as $reserv) {
                $dureeTotal = $reserv->getDuree() + $dureeTotal;
            }
            //cacul chiffre d'affaire
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
            'form' => $form->createView(),

        ]);
    }
}
