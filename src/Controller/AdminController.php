<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/backoffice")
 */
class AdminController extends AbstractController
{
  /**
   * @Route("/", name="admin_index", methods={"GET"})
   */
  public function index(): Response
  {

    return $this->render('admin/index.html.twig');
  }

  /**
   * @Route("/rechercher_res", name="rechercher_res")
   */
  public function rechercher_res(): Response
  {
    return $this->render('reservation/rechercher_res.html.twig');
  }

  /**
   * @Route("/contrats_en_cours", name="contrats_en_cours", methods={"GET"})
   */
  public function contrats_en_cours(): Response
  {
    return $this->render('reservation/contrat/en_cours/index.html.twig');
  }

  /**
   * @Route("/detail_contrats_en_cours", name="detail_contrats_en_cours", methods={"GET"})
   */
  public function detail_contrats_en_cours(): Response
  {
    return $this->render('reservation/contrat/en_cours/detail.html.twig');
  }

  /**
   * @Route("/contrats_termines", name="contrats_termines", methods={"GET"})
   */
  public function contrats_termines(): Response
  {
    return $this->render('reservation/contrat/termine/index.html.twig');
  }

  /**
   * @Route("/detail_contrat_termine", name="detail_contrat_termine", methods={"GET"})
   */
  public function detail_contrat_termine(): Response
  {
    return $this->render('reservation/contrat/termine/detail.html.twig');
  }

  /**
   * @Route("/nouvelle_reservation", name="nouvelle_reservation", methods={"GET"})
   */
  public function nouvelle_reservation(): Response
  {
    return $this->render('reservation/nouvelle_reservation.html.twig');
  }
  /**
   * @Route("/report_reservation", name="report_reservation", methods={"GET"})
   */
  public function report_reservation(): Response
  {
    return $this->render('reservation/report_reservation.html.twig');
  }

  /**
   * @Route("/reserv_non_solde", name="reserv_non_solde", methods={"GET"})
   */
  public function reserv_non_solde(): Response
  {
    return $this->render('reservation/reserv_non_solde.html.twig');
  }

  /**
   * @Route("/sans_vehicule", name="sans_vehicule", methods={"GET"})
   */
  public function sans_vehicule(): Response
  {
    return $this->render('reservation/sans_vehicule.html.twig');
  }

  /**
   * @Route("/echec_paiement", name="echec_paiement", methods={"GET"})
   */
  public function echec_paiement(): Response
  {
    return $this->render('reservation/echec_paiement/index.html.twig');
  }

  /**
   * @Route("/detail_echec_paiement", name="detail_echec_paiement", methods={"GET"})
   */
  public function detail_echec_paiement(): Response
  {
    return $this->render('reservation/echec_paiement/detail.html.twig');
  }

  /**
   * @Route("/devis_reservation", name="devis_reservation", methods={"GET"})
   */
  public function devis_reservation(): Response
  {
    return $this->render('reservation/devis/index.html.twig');
  }

  /**
   * @Route("/detail_devis", name="detail_devis", methods={"GET"})
   */
  public function detail_devis(): Response
  {
    return $this->render('reservation/detail.html.twig');
  }

  /**
   * @Route("/annulation_reservation", name="annulation_reservation", methods={"GET"})
   */
  public function annulation_reservation(): Response
  {
    return $this->render('reservation/annulation/index.html.twig');
  }


  /**
   * @Route("/annulation_attente", name="annulation_attente", methods={"GET"})
   */
  public function annulation_attente(): Response
  {
    return $this->render('reservation/annulation/attente.html.twig');
  }

  /**
   * @Route("/vente_comptoir", name="vente_comptoir", methods={"GET"})
   */
  public function vente_comptoir(): Response
  {
    return $this->render('vente_comptoir/index.html.twig');
  }
}
