<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\AnnulationReservationRepository;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationAnnulationController extends AbstractController
{
    private $reservationRepo;
    private $annulationReservationRepo;

    public function __construct(AnnulationReservationRepository $annulationReservationRepo, ReservationRepository $reservationRepo)
    {

        $this->reservationRepo = $reservationRepo;
        $this->annulationReservationRepo = $annulationReservationRepo;
    }
    /**
     * @Route("backoffice/reservation/annulation", name="reservation_cancel_index")
     */
    public function index(): Response
    {
        $annulations = $this->annulationReservationRepo->findAll();

        return $this->render('admin/reservation/annulation/index.html.twig', [
            'annulations' => $annulations
        ]);
    }
}
