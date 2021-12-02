<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Snappy\Pdf;
use App\Entity\Paiement;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Form\ReportResaType;
use App\Form\KilometrageType;
use App\Form\ReservationType;
use App\Service\TarifsHelper;
use App\Form\AjoutPaiementType;
use App\Form\EditClientReservationType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use App\Repository\ModePaiementRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContratsController extends AbstractController
{

    private $reservController;
    private $tarifsHelper;
    private $dateHelper;
    private $modePaiementRepo;
    private $em;
    private $flashy;

    public function __construct(FlashyNotifier $flashy, EntityManagerInterface $em, ModePaiementRepository $modePaiementRepo, DateHelper $dateHelper, TarifsHelper $tarifsHelper, ReservationController $reservController)
    {

        $this->reservController = $reservController;
        $this->tarifsHelper = $tarifsHelper;
        $this->dateHelper = $dateHelper;
        $this->modePaiementRepo = $modePaiementRepo;
        $this->em = $em;
        $this->flashy = $flashy;
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
     * @Route("/reservation/contrats_termines", name="contrats_termines_index", methods={"GET"})
     */
    public function termine(ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
    {

        $reservations = $reservationRepository->findReservationsTermines();
        return $this->render('admin/reservation/contrat/termine/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }
}
