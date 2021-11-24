<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationReportController extends AbstractController
{

    private $reservationRepo;

    public function __construct(Reservationrepository $reservationRepo)
    {
        $this->reservationRepo = $reservationRepo;
    }

    /**
     * @Route("/backoffice/reservations-reportés", name="reservation_reported_index", methods={"GET", "POST"},requirements={"id":"\d+"})
     */
    public function index(ReservationRepository $reservationRepository, Request $request): Response
    {
        $reservations = $this->reservationRepo->findReportedReservations();

        return $this->render('admin/reservation/report/index.html.twig', [
            'reservations' => $reservations
        ]);
    }
}
