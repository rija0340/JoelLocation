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
use App\Repository\AvisRepository;
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
use App\Service\SymfonyMailer;
use App\Service\VehiculeHelper;
use DoctrineExtensions\Query\Mysql\Format;
use GuzzleHttp\RetryMiddleware;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

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
  private $avisRepo;
  private $vehiculeHelper;
  private $symfonyMailer;

  public function __construct(
    AvisRepository $avisRepo,
    DevisRepository $devisRepo,
    EntityManagerInterface $em,
    MarqueRepository $marqueRepo,
    ModeleRepository $modeleRepo,
    TarifsHelper $tarifsHelper,
    DateHelper $dateHelper,
    TarifsRepository $tarifsRepo,
    ReservationRepository $reservationRepo,
    UserRepository $userRepo,
    VehiculeRepository $vehiculeRepo,
    OptionsRepository $optionsRepo,
    GarantieRepository $garantiesRepo,
    VehiculeHelper $vehiculeHelper,
    SymfonyMailer $symfonyMailer
  ) {

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
    $this->avisRepo = $avisRepo;
    $this->vehiculeHelper = $vehiculeHelper;
    $this->symfonyMailer = $symfonyMailer;
  }


  /**
   * @Route("/", name="admin_index", methods={"GET"})
   */
  public function index(): Response
  {

    //find by accept limitation de résultats , param => criteria, sorting, number of results
    $cinqDernieresreservations = $this->reservationRepo->findBy(['code_reservation' => 'devisTransformé'], ['id' => 'DESC'], 5);
    $devis = $this->devisRepo->findBy(['transformed' => true], ['id' => 'DESC'], 5);
    $stopSales = $this->reservationRepo->findBy(['code_reservation' => 'stopSale'], ['id' => 'DESC'], 5);
    $avis = $this->avisRepo->findBy(array(), ['id' => 'DESC'], 5);
    $vehicules = $this->vehiculeRepo->findAll();
    $modelesVehicules = $this->modeleRepo->findAll();

    // trouver toute les réservations sans stopSales
    $allReservations = $this->reservationRepo->findReservationsSansStopSales();
    //------------------------chiffre d'affaire total, web, cpt, ca --------------------------------------
    //trier les réservations par catégorie -> web, cpt, 
    $CA_WEB_moinsEncours = 0;
    $CA_CPT_moinsEncours = 0;
    $CA_anneeEnCours = 0;
    //chiffre d'affaire mois en cours
    foreach ($allReservations as $reservation) {
      if ($this->dateHelper->dateNow()->format('m') == $reservation->getDateDebut()->format('m')) {

        if ($reservation->getModeReservation() != null) {
          if ($reservation->getModeReservation()->getLibelle() == 'WEB') {
            $CA_WEB_moinsEncours = $CA_WEB_moinsEncours +  $reservation->getPrix();
          } else {
            $CA_CPT_moinsEncours = $CA_CPT_moinsEncours +  $reservation->getPrix();
          }
        }
      }
      //chiffre d'affaire année en cours
      if ($this->dateHelper->dateNow()->format('Y') == $reservation->getDateDebut()->format('Y')) {
        $CA_anneeEnCours = $CA_anneeEnCours + $reservation->getPrix();
      }
    }

    //***************************reservation par modeles de véhicules TAUX D'OCCUPATION CATEGORIES****************************************** */
    $parModele = [];
    foreach ($modelesVehicules as $modele) {
      $reservationsParModele = [];
      // mettre dans la table $reservationsParModele toutes les réservations concernées
      foreach ($allReservations as $reservation) {
        if ($reservation->getVehicule()->getModele() == $modele) {
          array_push($reservationsParModele, $reservation);
        }
      }
      //trier la table $reservationParModele par mois, mois courant et les 5 mois à venir
      //cela consiste à indiquer nombre de reservation par mois pour un modele
      //exemple : 
      // novembre 2021 => 2 réservations
      // décembre 2021 => 4 réservations
      //.... 
      //mois courant et les 5 prochains à venir
      $dateNow = new \DateTime('now');
      $currentMonth = $dateNow->format('m');
      $currentYear = $dateNow->format('Y');

      //pour current day , on a choisi le 15 parceque fevrier n'a que 29 et les autres n'ont que 30 jours
      $currentDate =   new \DateTime($currentYear . "-" . $currentMonth  . "-" . "15");

      $reservationParMois = [];
      for ($i = 0; $i < 6; $i++) {
        $somme = 0;
        //compter les réservations correspondant à chaque mois (mois courant et les 5 mois à venir)
        foreach ($reservationsParModele as $reservation) {
          if ($reservation->getDateDebut()->format('m') == $currentDate->format('m') && $reservation->getDateDebut()->format('Y') == $currentDate->format('Y')) {
            $somme = $somme + 1;
          }
        }
        //mettre dans un tableau key valeur , mois => nombre reservations, pour un modele
        $reservationParMois[$this->dateHelper->getMonthFullName($currentDate) . " " . $currentDate->format('Y')] = $somme;
        $currentDate = $currentDate->modify("next month");
      }

      //inserer dans un table key valeur , modele=> tableau(contenant mois-annee => nombre reservations)
      //nombre de véhicules par modele inclus
      //exemple : [Renault Clio => ['parMois' => [Novembre 2021' => 10, 'Décembre 2021'=> 5 ], 'nombreVehicules'=> 2]] 
      $parModele[$modele->getMarque()->getLibelle() . " " . $modele->getLibelle()]['parMois'] = $reservationParMois;
      $parModele[$modele->getMarque()->getLibelle() . " " . $modele->getLibelle()]['nombreVehicules'] = $modele->getVehicules()->count();
    }
    //********************************************************reservation parc véhicules en général par mois TAUX D'OCCUPATION GENERAL ******************************** */
    $reservationsParcVehicules = [];
    for ($i = 0; $i < 12; $i++) {
      $dates = new \DateTime('01 january +' . $i . 'month');
      $nombre = 0;
      foreach ($allReservations as $reservation) {
        if ($reservation->getDateDebut()->format('m') == $dates->format('m') && $reservation->getDateDebut()->format('Y') == $dates->format('Y')) {
          $nombre = $nombre + 1;
        }
        $reservationsParcVehicules[$this->dateHelper->getMonthFullName($dates) . " " . $dates->format('Y')] = $nombre;
      }
    }

    return $this->render('admin/index.html.twig', [
      'cinqDernieresreservations' => $cinqDernieresreservations,
      'stopSales' => $stopSales,
      'devis' => $devis,
      'avis' => $avis,
      'vehicules' => $vehicules,
      'reservationsParModele' => $parModele, //reservation categorisé par modèle
      'reservationsParcVehicules' => $reservationsParcVehicules, //reservation parc véhicule en général par mois
      'CA_WEB_moinsEncours' => $CA_WEB_moinsEncours,
      'CA_CPT_moinsEncours' => $CA_CPT_moinsEncours,
      'CA_anneeEnCours' => $CA_anneeEnCours,
    ]);
  }



  /**
   * @Route("/reserv_non_solde_detail", name="reserv_non_solde_detail", methods={"GET"})
   */
  public function reserv_non_solde_detail(): Response
  {
    return $this->render('admin/reservation/non_solde/detail.html.twig');
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
   * @Route("/envoi-email", name="envoi_email", methods={"GET","POST"})
   */
  public function envoiEmail()
  {
    try {
      $this->symfonyMailer->send("mety sa tsy mety", "contact@joellocation@gmail.com", "rakotoarinelinarija@gmail.com", "", []);
      die('tonga ato za');
    } catch (TransportExceptionInterface $e) {
      echo '<pre>';
      print_r($e, TRUE);
      echo '</pre>';
      die('ato amle erreur zazao');
    }
    die('ivelany');
  }
}
