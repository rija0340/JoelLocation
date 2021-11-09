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
use App\Repository\ConducteurRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    private $em;
    private $conducteurRepo;


    public function __construct(
        ReservationRepository $reservationRepo,
        DevisRepository $devisRepo,
        OptionsRepository $optionsRepo,
        GarantieRepository $garantiesRepo,
        DateHelper $dateHelper,
        TarifsHelper $tarifsHelper,
        VehiculeRepository $vehiculeRepo,
        FlashyNotifier $flashy,
        ValidationReservationClientSession $validationSession,
        EntityManagerInterface $em,
        ConducteurRepository $conducteurRepo


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
        $this->em = $em;
        $this->conducteurRepo = $conducteurRepo;
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
     * @Route("espaceclient/details-reservation/{id}", name="client_reservation_show", methods={"GET", "POST"},requirements={"id":"\d+"})
     */
    public function detailsReservation(Reservation $reservation, Request $request): Response
    {
        $conducteurs =  $this->conducteurRepo->findBy(['client' => $this->getUser(), 'reservation' => null]);
        return $this->render('client/reservation/details_reservation.html.twig', [
            'reservation' => $reservation,
            'conducteurs' => $conducteurs
        ]);
    }


    //*******************************conducteur dans details reservation****************************************
    /**
     *  @Route("espaceclient/ajouter-conducteur/{id}", name="client_add_conducteur", methods={"GET","POST"},requirements={"id":"\d+"})
     *
     */
    public function addConducteur(Request $request, Reservation $reservation): Response
    {

        if ($request->request->get('conducteur') != null) {

            $conducteur = $this->conducteurRepo->find($request->request->get('conducteur'));
            $reservation->addConducteursClient($conducteur);
            $this->em->flush();
            $this->flashy->success("Le conducteur a été ajouté avec succès");

            return $this->redirectToRoute('client_reservation_show', ['id' => $reservation->getId()]);
        }
        return $this->redirectToRoute('client_reservation_show', ['id' => $reservation->getId()]);
    }
    /** 
     *  @Route("espaceclient/conducteur-principal/{id}", name="make_conducteur_principal", methods={"GET","POST"},requirements={"id":"\d+"})
     */
    public function makeConducteurPrincipal(Request $request, Reservation $reservation)
    {
        return $this->redirectToRoute('client_reservation_show', ['id ' => $reservation->getId()]);
    }


    /**
     * @Route("espaceclient/supprimer-conducteur/{id}", name="client_conducteur_delete", methods={"DELETE"},requirements={"id":"\d+"})
     */
    public function deleteConducteur(Request $request, Conducteur $conducteur): Response
    {
        $id = $this->reservationRepo->find($request->request->get('reservation'));
        $reservation = $this->reservationRepo->find($id);
        if ($this->isCsrfTokenValid('delete' . $conducteur->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($conducteur);
            $entityManager->flush();
            $this->flashy->success('le conducteur a été supprimé');
            return $this->redirectToRoute('client_reservations');
        }
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
