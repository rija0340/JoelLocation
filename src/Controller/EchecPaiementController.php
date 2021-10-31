<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\PaiementRepository;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EchecPaiementController extends AbstractController
{

    private $paiementRepo;
    private $reservationRepo;

    public function __construct(
        PaiementRepository $paiementRepo,
        ReservationRepository $reservationRepo
    ) {
        $this->paiementRepo = $paiementRepo;
        $this->reservationRepo = $reservationRepo;
    }

    /**
     * @Route("backoffice/reservation/echec-paiement", name="echec_paiement_index")
     */
    public function index(): Response
    {

        $reservations = $this->reservationRepo->findReservationsSansStopSales();

        $listeReservations = [];

        foreach ($reservations as $reservation) {
            if ($reservation->getSommePaiements() == 0) {
                array_push($listeReservations, $reservation);
            }
        }

        return $this->render('admin/reservation/echec_paiement/index.html.twig', [
            'reservations' => $listeReservations

        ]);
    }

    /**
     * @Route("backoffice/reservation/echec-paiement/details/{id}", name="echec_paiement_show")
     */
    public function show(Request $request, Reservation $reservation): Response
    {

        return $this->render('admin/reservation/echec_paiement/details.html.twig', [
            'reservation' => $reservation
        ]);
    }
}
