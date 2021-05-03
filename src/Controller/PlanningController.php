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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PlanningController extends AbstractController
{



    /**
     * @Route("/planningGeneralData", name="planningGeneralData", methods={"GET"})
     */

    public function planningGeneralData(Request $request, ReservationRepository $reservationRepo,  NormalizerInterface $normalizer)
    {

        $reservations = $reservationRepo->findAll();
        // dd($reservations);
        // $reservationsNormalises = $normalizer->normalize($reservations, null, ['groups' => 'reserv:read']);
        // $json = json_encode($reservationsNormalises);
        $datas = array();
        foreach ($reservations as $key => $reservation) {
            $datas[$key]['id'] = $reservation->getId();
            $datas[$key]['text'] = $reservation->getType();
            $datas[$key]['start_date_formated'] = $reservation->getDateDebut()->format('d/m/Y');
            $datas[$key]['end_date_formated'] = $reservation->getDateFin()->format('d/m/Y');
            $datas[$key]['start_date'] = $reservation->getDateDebut();
            $datas[$key]['end_date'] = $reservation->getDateFin();
            $datas[$key]['client_name'] = $reservation->getClient()->getNom() . " " .  $reservation->getClient()->getPrenom();
        }

        return new JsonResponse($datas);
    }


    /**
     * @Route("/planning-general", name="planGen", methods={"GET"})
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
