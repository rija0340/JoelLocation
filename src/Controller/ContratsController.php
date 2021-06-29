<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContratsController extends AbstractController
{

    /**
     * @Route("/reservation/contrats_en_cours", name="contrats_en_cours_index", methods={"GET"})
     */
    public function enCours(ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
    {

        $reservations = $reservationRepository->findReservationsSansStopSales();

        return $this->render('admin/reservation/contrat/en_cours/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }


    /**
     * @Route("/reservation/contrats_en_cours/{id}", name="contrats_show", methods={"GET"})
     */
    public function showEnCours(Reservation $reservation): Response
    {

        return $this->render('admin/reservation/contrat/en_cours/details.html.twig', [
            'reservation' => $reservation,
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
     * @Route("/reservation/contrat_termine/{id}", name="contrat_termine_show", methods={"GET"})
     */
    public function showTermine(Reservation $reservation): Response
    {

        return $this->render('admin/reservation/contrat/termine/details.html.twig', [
            'reservation' => $reservation,
        ]);
    }
}
