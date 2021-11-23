<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Snappy\Pdf;
use App\Entity\Paiement;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Form\KilometrageType;
use App\Service\TarifsHelper;
use App\Form\AjoutPaiementType;
use App\Repository\ReservationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContratsController extends AbstractController
{

    private $reservController;
    private $tarifsHelper;
    private $dateHelper;

    public function __construct(DateHelper $dateHelper, TarifsHelper $tarifsHelper, ReservationController $reservController)
    {

        $this->reservController = $reservController;
        $this->tarifsHelper = $tarifsHelper;
        $this->dateHelper = $dateHelper;
    }

    /**
     * @Route("/reservation/contrats_en_cours", name="contrats_en_cours_index", methods={"GET"})
     */
    public function enCours(ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
    {

        $reservations = $reservationRepository->findReservationIncludeDate($this->dateHelper->dateNow());

        return $this->render('admin/reservation/contrat/en_cours/index.html.twig', [
            'reservations' => $reservations,

        ]);
    }

    /**
     * @Route("/reservation/contrats_en_cours/{id}", name="contrats_show", methods={"GET"})
     */
    public function showEnCours(Reservation $reservation, Request $request): Response
    {
        $vehicule = $reservation->getVehicule();
        $formKM = $this->createForm(KilometrageType::class, $vehicule);
        $formKM->handleRequest($request);
        $conducteurs =  $reservation->getConducteursClient();
        $conducteur = $conducteurs[0];

        //form pour ajout paiement
        $formAjoutPaiement = $this->createForm(AjoutPaiementType::class);
        $formAjoutPaiement->handleRequest($request);

        if ($formKM->isSubmitted() && $formKM->isValid()) {

            $this->em->persist($vehicule);
            $this->em->flush();

            return $this->render('admin/reservation/contrat/en_cours/details.html.twig', [
                'reservation' => $reservation,
                'formKM' => $formKM->createView(),
                'formAjoutPaiement' => $formAjoutPaiement->createView()

            ]);
        }

        if ($formAjoutPaiement->isSubmitted() && $formAjoutPaiement->isValid()) {

            // enregistrement montant et reservation dans table paiement 
            $paiement  = new Paiement();
            $paiement->setClient($reservation->getClient());
            $paiement->setDatePaiement($this->dateHelper->dateNow());
            $paiement->setMontant($formAjoutPaiement->getData()['montant']);
            $paiement->setReservation($reservation);
            $paiement->setModePaiement($this->modePaiementRepo->findOneBy(['libelle' => 'ESPECE']));
            $paiement->setMotif("Réservation");
            $this->em->persist($paiement);
            $this->em->flush();

            // notification pour réussite enregistrement
            $this->flashy->success("L'ajout du paiement a été effectué avec succès");
            return $this->redirectToRoute($this->getRouteForRedirection($reservation), ['id' => $reservation->getId()]);
        }
        return $this->render('admin/reservation/contrat/en_cours/details.html.twig', [
            'reservation' => $reservation,
            'formKM' => $formKM->createView(),
            'formAjoutPaiement' => $formAjoutPaiement->createView()


        ]);
    }

    /**
     * @Route("/reservation/contrats_termines", name="contrats_termines_index", methods={"GET"})
     */
    public function termine(ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
    {

        $reservations = $reservationRepository->findReservationsTermines();

        return $this->render('admin/reservation/contrat/termine/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    /**
     * @Route("/reservation/contrat_termine/{id}", name="contrat_termine_show",methods={"GET","POST"})
     */
    public function showTermine(Reservation $reservation, Request $request): Response
    {
        $vehicule = $reservation->getVehicule();
        $formKM = $this->createForm(KilometrageType::class, $vehicule);
        $formKM->handleRequest($request);
        //form pour ajout paiement
        $formAjoutPaiement = $this->createForm(AjoutPaiementType::class);
        $formAjoutPaiement->handleRequest($request);

        if ($formKM->isSubmitted() && $formKM->isValid()) {

            $this->em->persist($vehicule);
            $this->em->flush();

            return $this->render('admin/reservation/contrat/termine/details.html.twig', [
                'reservation' => $reservation,
                'formKM' => $formKM->createView(),
                'formAjoutPaiement' => $formAjoutPaiement->createView()


            ]);
        }

        if ($formAjoutPaiement->isSubmitted() && $formAjoutPaiement->isValid()) {

            // enregistrement montant et reservation dans table paiement 
            $paiement  = new Paiement();
            $paiement->setClient($reservation->getClient());
            $paiement->setDatePaiement($this->dateHelper->dateNow());
            $paiement->setMontant($formAjoutPaiement->getData()['montant']);
            $paiement->setReservation($reservation);
            $paiement->setModePaiement($this->modePaiementRepo->findOneBy(['libelle' => 'ESPECE']));
            $paiement->setMotif("Réservation");
            $this->em->persist($paiement);
            $this->em->flush();

            // notification pour réussite enregistrement
            $this->flashy->success("L'ajout du paiement a été effectué avec succès");
            return $this->redirectToRoute($this->getRouteForRedirection($reservation), ['id' => $reservation->getId()]);
        }
        return $this->render('admin/reservation/contrat/termine/details.html.twig', [
            'reservation' => $reservation,
            'formKM' => $formKM->createView(),
            'formAjoutPaiement' => $formAjoutPaiement->createView()

        ]);
    }

    /**
     * @Route("reservation/kilometrage/{id}", name="reservation_delete", methods={"DELETE"},requirements={"id":"\d+"})
     */
    public function kilometrage(Request $request, Reservation $reservation): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('reservation_index');
    }

    //return route en fonction date (comparaison avec dateNow pour savoir statut réservation)
    public function getRouteForRedirection($reservation)
    {

        $dateDepart = $reservation->getDateDebut();
        $dateRetour = $reservation->getDateFin();
        $dateNow = $this->dateHelper->dateNow();

        //classement des réservations

        // 1-nouvelle réservation -> dateNow > dateReservation
        if ($dateNow < $dateDepart) {
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
