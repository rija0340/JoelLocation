<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\KilometrageType;
use App\Repository\ReservationRepository;
use App\Service\TarifsHelper;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContratsController extends AbstractController
{


    private $reservController;
    private $tarifsHelper;


    public function __construct(TarifsHelper $tarifsHelper, ReservationController $reservController)
    {

        $this->reservController = $reservController;
        $this->tarifsHelper = $tarifsHelper;
    }

    /**
     * @Route("/reservation/contrats_en_cours", name="contrats_en_cours_index", methods={"GET"})
     */
    public function enCours(ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
    {

        $reservations = $reservationRepository->findReservationIncludeDate(new \DateTime('NOW'));

        return $this->render('admin/reservation/contrat/en_cours/index.html.twig', [
            'reservations' => $reservations,

        ]);
    }

    /**
     * @Route("/reservation/contrats_en_cours/{id}", name="contrats_show", methods={"GET"})
     */
    public function showEnCours(Reservation $reservation, Request $request): Response
    {
        $formKM = $this->createForm(KilometrageType::class, $reservation);
        $formKM->handleRequest($request);


        if ($formKM->isSubmitted() && $formKM->isValid()) {

            $entityManager = $this->reservController->getDoctrine()->getManager();
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->render('admin/reservation/contrat/termine/details.html.twig', [
                'reservation' => $reservation,
                'formKM' => $formKM->createView(),
                'tarifVehicule' => $this->tarifsHelper->calculTarifVehicule($reservation->getDateDebut(), $reservation->getDateFin(), $reservation->getVehicule()),
                'tarifOptions' => $this->tarifsHelper->sommeTarifsOptions($reservation->getOptions()),
                'tarifGaranties' => $this->tarifsHelper->sommeTarifsGaranties($reservation->getGaranties()),

            ]);
        }


        return $this->render('admin/reservation/contrat/termine/details.html.twig', [
            'reservation' => $reservation,
            'formKM' => $formKM->createView(),
            'tarifVehicule' => $this->tarifsHelper->calculTarifVehicule($reservation->getDateDebut(), $reservation->getDateFin(), $reservation->getVehicule()),
            'tarifOptions' => $this->tarifsHelper->sommeTarifsOptions($reservation->getOptions()),
            'tarifGaranties' => $this->tarifsHelper->sommeTarifsGaranties($reservation->getGaranties()),
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
        $formKM = $this->createForm(KilometrageType::class, $reservation);
        $formKM->handleRequest($request);


        if ($formKM->isSubmitted() && $formKM->isValid()) {

            $entityManager = $this->reservController->getDoctrine()->getManager();
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->render('admin/reservation/contrat/termine/details.html.twig', [
                'reservation' => $reservation,
                'formKM' => $formKM->createView(),
                'tarifVehicule' => $this->tarifsHelper->calculTarifVehicule($reservation->getDateDebut(), $reservation->getDateFin(), $reservation->getVehicule()),
                'tarifOptions' => $this->tarifsHelper->sommeTarifsOptions($reservation->getOptions()),
                'tarifGaranties' => $this->tarifsHelper->sommeTarifsGaranties($reservation->getGaranties()),

            ]);
        }

        return $this->render('admin/reservation/contrat/termine/details.html.twig', [
            'reservation' => $reservation,
            'formKM' => $formKM->createView(),
            'tarifVehicule' => $this->tarifsHelper->calculTarifVehicule($reservation->getDateDebut(), $reservation->getDateFin(), $reservation->getVehicule()),
            'tarifOptions' => $this->tarifsHelper->sommeTarifsOptions($reservation->getOptions()),
            'tarifGaranties' => $this->tarifsHelper->sommeTarifsGaranties($reservation->getGaranties()),
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
}
