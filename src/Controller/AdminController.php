<?php

namespace App\Controller;

use DateTime;
use App\Classe\Mail;
use App\Classe\Reservation as ClasseReservation;
use App\Entity\Tarifs;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Form\RechercheAVType;
use App\Form\ReservationType;
use App\Service\TarifsHelper;
use App\Form\ReservationStep1Type;
use App\Repository\DevisRepository;
use App\Repository\UserRepository;
use App\Repository\MarqueRepository;
use App\Repository\ModeleRepository;
use App\Repository\TarifsRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use GuzzleHttp\RetryMiddleware;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/backoffice")
 */
class AdminController extends AbstractController
{

  private $userRepo;
  private $reservationRepo;
  private $dateTimestamp;
  private $vehiculeRepo;
  private $modeleRepo;
  private $optionsRepo;
  private $garantiesRepo;
  private $tarifsRepo;
  private $dateHelper;
  private $tarifsHelper;
  private $marqueRepo;
  private $em;
  private $devisRepo;

  public function __construct(DevisRepository $devisRepo, EntityManagerInterface $em, MarqueRepository $marqueRepo, ModeleRepository $modeleRepo, TarifsHelper $tarifsHelper, DateHelper $dateHelper, TarifsRepository $tarifsRepo, ReservationRepository $reservationRepo,  UserRepository $userRepo, VehiculeRepository $vehiculeRepo, OptionsRepository $optionsRepo, GarantieRepository $garantiesRepo)
  {

    $this->reservationRepo = $reservationRepo;
    $this->vehiculeRepo = $vehiculeRepo;
    $this->optionsRepo = $optionsRepo;
    $this->garantiesRepo = $garantiesRepo;
    $this->tarifsRepo = $tarifsRepo;
    $this->userRepo = $userRepo;
    $this->dateHelper = $dateHelper;
    $this->tarifsHelper = $tarifsHelper;
    $this->modeleRepo = $modeleRepo;
    $this->marqueRepo = $marqueRepo;
    $this->em = $em;
    $this->devisRepo = $devisRepo;
  }


  /**
   * @Route("/", name="admin_index", methods={"GET"})
   */
  public function index(): Response
  {
    $reservations = $this->reservationRepo->findReservationsSansStopSales(array(), array('id' => 'DESC'), 5);
    $devis = $this->devisRepo->findDevisTransformes(array(), array('id' => 'DESC'), 5);
    $stopSales = $this->reservationRepo->findStopSales(array(), array('id' => 'DESC'), 5);

    return $this->render('admin/index.html.twig', [
      'reservations' => $reservations,
      'stopSales' => $stopSales,
      'devis' => $devis
    ]);
  }


  /**
   * @Route("/contrats_termines", name="contrats_termines", methods={"GET"})
   */
  public function contrats_termines(): Response
  {
    return $this->render('admin/reservation/contrat/termine/index.html.twig');
  }

  /**
   * @Route("/detail_contrat_termine", name="detail_contrat_termine", methods={"GET"})
   */
  public function detail_contrat_termine(): Response
  {
    return $this->render('admin/reservation/contrat/termine/detail.html.twig');
  }

  /**
   * @Route("/nouvelle_reservation", name="nouvelle_reservation", methods={"GET"})
   */
  public function nouvelle_reservation(): Response
  {
    return $this->render('admin/reservation/nouvelle_reservation.html.twig');
  }
  /**
   * @Route("/report_reservation", name="report_reservation", methods={"GET"})
   */
  public function report_reservation(): Response
  {
    return $this->render('admin/reservation/report_reservation.html.twig');
  }

  /**
   * @Route("/reserv_non_solde", name="reserv_non_solde", methods={"GET"})
   */
  public function reserv_non_solde(): Response
  {
    return $this->render('admin/reservation/non_solde/reserv_non_solde.html.twig');
  }

  /**
   * @Route("/reserv_non_solde_detail", name="reserv_non_solde_detail", methods={"GET"})
   */
  public function reserv_non_solde_detail(): Response
  {
    return $this->render('admin/reservation/non_solde/detail.html.twig');
  }


  /**
   * @Route("/echec_paiement", name="echec_paiement", methods={"GET"})
   */
  public function echec_paiement(): Response
  {
    return $this->render('admin/reservation/echec_paiement/index.html.twig');
  }

  /**
   * @Route("/detail_echec_paiement", name="detail_echec_paiement", methods={"GET"})
   */
  public function detail_echec_paiement(): Response
  {
    return $this->render('admin/reservation/echec_paiement/detail.html.twig');
  }

  /**
   * @Route("/devis_reservation", name="devis_reservation", methods={"GET"})
   */
  public function devis_reservation(): Response
  {
    return $this->render('admin/reservation/devis/index.html.twig');
  }

  /**
   * @Route("/detail_devis", name="detail_devis", methods={"GET"})
   */
  public function detail_devis(): Response
  {
    return $this->render('admin/reservation/devis/detail.html.twig');
  }

  /**
   * @Route("/annulation_reservation", name="annulation_reservation", methods={"GET"})
   */
  public function annulation_reservation(): Response
  {
    return $this->render('admin/reservation/annulation/index.html.twig');
  }


  /**
   * @Route("/annulation_attente", name="annulation_attente", methods={"GET"})
   */
  public function annulation_attente(): Response
  {
    return $this->render('admin/reservation/annulation/attente.html.twig');
  }

  /**
   * @Route("/annulation_avoir", name="annulation_avoir", methods={"GET"})
   */
  public function annulation_avoir(): Response
  {
    return $this->render('admin/reservation/annulation/avec_avoir.html.twig');
  }


  /**
   * @Route("/vente_comptoir", name="vente_comptoir", methods={"GET"})
   */
  public function vente_comptoir(TarifsRepository $tarifsRepo, GarantieRepository $garantieRepo, OptionsRepository $optionsRepo): Response
  {
    $garanties = $garantieRepo->findAll();

    $tarifs = new Tarifs();
    $tarifs = $tarifsRepo->findAll();
    $options = $optionsRepo->findAll();
    return $this->render('admin/vente_comptoir/index.html.twig', [
      'tarifs' => $tarifs,
      'garanties' => $garanties,
      'options' => $options,
    ]);
  }

  /**
   * @Route("/appel_paiement", name="appel_paiement", methods={"GET"})
   */
  public function appel_paiement(): Response
  {
    return $this->render('admin/reservation/appel_paiement/index.html.twig');
  }

  /**
   * @Route("/chiffre_affaire", name="chiffre_affaire", methods={"GET"})
   */
  public function chiffre_affaire(): Response
  {
    return $this->render('admin/chiffre_affaire/index.html.twig');
  }

  /**
   * @Route("/paiement", name="paiement", methods={"GET"})
   */
  public function paiement(): Response
  {
    return $this->render('admin/paiement/index.html.twig');
  }
}
