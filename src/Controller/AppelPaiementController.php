<?php

namespace App\Controller;

use App\Repository\PaiementRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppelPaiementController extends AbstractController
{

    private $paiementRepo;
    private $reservationRepo;
    public function __construct(PaiementRepository $paiementRepo, ReservationRepository $reservationRepo)
    {
        $this->paiementRepo = $paiementRepo;
        $this->reservationRepo = $reservationRepo;
    }

    /**
     * @Route("backoffice/appel-paiement", name="appel_paiement_index")
     */
    public function index(): Response
    {
        $reservations = $this->reservationRepo->findAppelPaiement();
        return $this->render('admin/reservation/appel_paiement/index.html.twig', [
            'reservations' => $reservations
        ]);
    }
}
