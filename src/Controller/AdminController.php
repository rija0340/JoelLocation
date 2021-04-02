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
    return $this->render('reservation/contrats_en_cours.html.twig');
  }

  /**
   * @Route("/contrats_termines", name="contrats_termines", methods={"GET"})
   */
  public function contrats_terminés(): Response
  {
    return $this->render('reservation/contrats_termines.html.twig');
  }
  /**
   * @Route("/details_reservation", name="details_reservation", methods={"GET"})
   */
  public function details_reservation(): Response
  {
    return $this->render('reservation/contrats_en_cours/details_reservation.html.twig');
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
}
