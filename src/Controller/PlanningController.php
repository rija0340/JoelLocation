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


class PlanningController extends AbstractController
{
    /**
     * @Route("/planning-général", name="planGen", methods={"GET"})
     */
    public function planGen(): Response
    {

    	return $this->render('planning/planGen.html.twig');
    }


     /**
     * @Route("/planning-journalier", name="planJour", methods={"GET"})
     */
     public function planJour(): Response
     {

     	return $this->render('planning/planJour.html.twig');
     }

         /**
     * @Route("/planning-périodique", name="planPerio", methods={"GET"})
     */
         public function planPerio(): Response
         {

            return $this->render('planning/planPerio.html.twig');
        }

    /**
     * @Route("/Véhicule-dispo", name="vehiculeDispo", methods={"GET"})
     */
    public function VehiculeDispo(): Response
    {

        return $this->render('planning/vehicule_dispo.html.twig');
    }
}