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
use DoctrineExtensions\Query\Mysql\Format;
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
  private $avisRepo;

  public function __construct(AvisRepository $avisRepo, DevisRepository $devisRepo, EntityManagerInterface $em, MarqueRepository $marqueRepo, ModeleRepository $modeleRepo, TarifsHelper $tarifsHelper, DateHelper $dateHelper, TarifsRepository $tarifsRepo, ReservationRepository $reservationRepo,  UserRepository $userRepo, VehiculeRepository $vehiculeRepo, OptionsRepository $optionsRepo, GarantieRepository $garantiesRepo)
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
    $this->avisRepo = $avisRepo;
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
    //***************************reservation par modeles de véhicules****************************************** */
    $parModele = [];
    foreach ($modelesVehicules as $modele) {
      $reservationsParModele = [];
      // mettre dans la table $reservationsParModele toutes les réservations concerné
      foreach ($allReservations as $reservation) {
        if ($reservation->getVehicule()->getModele() == $modele) {
          array_push($reservationsParModele, $reservation);
        }
      }
      //trier la table $reservationParModele par mois, mois courant et les 5 mois à venir
      //cela consiste à indiquer nombre de reservation par mois pour un modele
      // novembre 2021 => 2 
      // décembre 2021 => 4
      //.... 
      //mois courant et les 5 prochains à venir
      $reservationParMois = [];
      for ($i = 0; $i < 7; $i++) {
        $somme = 0;
        $currentDate =   new \DateTime("now " . "+" . $i . "month");
        foreach ($reservationsParModele as $reservation) {
          if ($reservation->getDateDebut()->format('m') == $currentDate->format('m') && $reservation->getDateDebut()->format('Y') == $currentDate->format('Y')) {
            $somme = $somme + 1;
          }
        }
        //mettre dans un tableau key valeur , mois => nombre reservations, pour un modele
        $reservationParMois[$this->dateHelper->getMonthFullName($currentDate) . " " . $currentDate->format('Y')] = $somme;
      }

      //inserer dans un table key valeur , modele=> tableau(contenant mois-annee => nombre reservations)
      //nombre de véhicules par modele inclus
      //exemple : [Renault Clio => ['parMois' => [Novembre 2021' => 10, 'Décembre 2021'=> 5 ], 'nombreVehicules'=> 2]] 
      $parModele[$modele->getMarque()->getLibelle() . " " . $modele->getLibelle()]['parMois'] = $reservationParMois;
      $parModele[$modele->getMarque()->getLibelle() . " " . $modele->getLibelle()]['nombreVehicules'] = $modele->getVehicules()->count();
    }
    //********************************************************reservation parc véhicules en général par mois******************************** */
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
      'reservationsParcVehicules' => $reservationsParcVehicules //reservation parc véhicule en général par mois
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
}
