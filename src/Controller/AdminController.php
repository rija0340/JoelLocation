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
 * @Route("/admin")
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
        return $this->render('reservation/details_reservation.html.twig');
      }

    }