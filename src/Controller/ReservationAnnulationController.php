<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationAnnulationController extends AbstractController
{
    private $reservationRepo;

    public function __construct(ReservationRepository $reservationRepo)
    {

        $this->reservationRepo = $reservationRepo;
    }
    /**
     * @Route("backoffice/reservation/annulation", name="reservation_cancel_index")
     */
    public function index(): Response
    {
        $reservations = $this->reservationRepo->findBy(['canceled' => true]);

        return $this->render('admin/reservation/annulation/index.html.twig', [
            'reservations' => $reservations
        ]);
    }
}
