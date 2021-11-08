<?php

namespace App\Controller\Client;

use Stripe\Stripe;
use App\Entity\Devis;
use App\Entity\Conducteur;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Form\ClientInfoType;
use App\Form\ConducteurType;
use Stripe\Checkout\Session;
use App\Form\DevisClientType;
use App\Service\TarifsHelper;
use App\Classe\ReservationClient;
use App\Repository\DevisRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Classe\ValidationReservationClientSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{

    private $reservationRepo;
    private $flashy;
    private $devisRepo;
    private $garantiesRepo;
    private $optionsRepo;
    private $dateHelper;
    private $tarifsHelper;
    private $vehiculeRepo;
    private $validationSession;

    public function __construct(
        ReservationRepository $reservationRepo,
        DevisRepository $devisRepo,
        OptionsRepository $optionsRepo,
        GarantieRepository $garantiesRepo,
        DateHelper $dateHelper,
        TarifsHelper $tarifsHelper,
        VehiculeRepository $vehiculeRepo,
        FlashyNotifier $flashy,
        ValidationReservationClientSession $validationSession

    ) {
        $this->reservationRepo = $reservationRepo;
        $this->devisRepo = $devisRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->optionsRepo = $optionsRepo;
        $this->dateHelper = $dateHelper;
        $this->tarifsHelper = $tarifsHelper;
        $this->vehiculeRepo = $vehiculeRepo;
        $this->flashy = $flashy;
        $this->validationSession = $validationSession;
    }

    /** 
     * @Route("/espaceclient/reservations", name="client_reservations", methods={"GET","POST"})
     */
    public function index(Request $request): Response
    {
        $client = $this->getUser();
        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }

        $date = $this->dateHelper->dateNow();

        //récupération des réservations effectuée
        $reservationEffectuers = $this->reservationRepo->findReservationEffectuers($client, $date);

        //récupération des réservations en cours
        $reservationEncours = $this->reservationRepo->findReservationEncours($client, $date);

        $res_attente_dateDebut = $this->reservationRepo->findReservationsAttenteDateDebut($client, $date);

        //récupération des réservation en attente (devis envoyé et en attente de validation par client)
        // $reservationEnAttentes = $this->reservRepo->findReservationEnAttente($client, $date);
        $devis = $this->devisRepo->findBy(['client' => $client, 'transformed' => false], ['dateCreation' => 'DESC']);

        return $this->render('client/reservation/index.html.twig', [
            'reservation_effectuers' => $reservationEffectuers,
            'reservation_en_cours' => $reservationEncours,
            'devis' => $devis,
            'res_attente_dateDebut' => $res_attente_dateDebut,
        ]);
    }

    /**
     *  @Route("espaceclient/ajouter-conducteur/{reference}", name="client_add_conducteur", methods={"GET","POST"},requirements={"id":"\d+"})
     *
     */
    public function addConducteur(Request $request, Reservation $reservation): Response
    {
        $conducteur = new Conducteur();
        $formConducteur = $this->createForm(ConducteurType::class);

        if ($formConducteur->isSubmitted() && $formConducteur->isValid()) {
            dump($formConducteur->getData());
        }
        return $this->render('client/conducteur/new.html.twig', [
            'formConducteur' => $formConducteur
        ]);
    }

    /**
     * @Route("espaceclient/details-reservation/{reference}", name="client_reservation_show", methods={"GET", "POST"},requirements={"id":"\d+"})
     */
    public function detailsReservation(Reservation $reservation, Request $request): Response
    {

        return $this->render('client/reservation/details_reservation.html.twig', [
            'reservation' => $reservation
        ]);
    }

    //return route en fonction date (comparaison avec dateNow pour savoir statut réservation)
    public function getRouteForRedirection($reservation)
    {

        $dateDepart = $reservation->getDateDebut();
        $dateRetour = $reservation->getDateFin();
        $dateNow = $this->dateHelper->dateNow();

        //classement des réservations

        // 1-nouvelle réservation -> dateNow > dateReservation
        if ($dateNow > $dateDepart) {
            $routeReferer = 'reservation_show';
        }
        if ($dateDepart < $dateNow && $dateNow < $dateRetour) {
            $routeReferer = 'contrats_show';
        }
        if ($dateNow > $dateRetour) {
            $routeReferer = 'contrat_termine_show';
        }
        return $routeReferer;
    }
}
