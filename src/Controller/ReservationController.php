<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\VehiculeRepository;
use App\Repository\ReservationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/reservation")
 */
class ReservationController extends AbstractController
{
    private $reservationRepo;
    private $dateTimestamp;
    private $vehiculeRepo;


    public function __construct(ReservationRepository $reservationRepo, VehiculeRepository $vehiculeRepo)
    {

        $this->reservationRepo = $reservationRepo;
        $this->vehiculeRepo = $vehiculeRepo;
    }

    /**
     * @Route("/vehiculeDispoFonctionDates", name="vehiculeDispoFonctionDates",methods={"GET","POST"}))
     */
    public function vehiculeDispoFonctionDates(Request $request)
    {

        // avy any amin'ny request ity (AJAX)
        $dateDebutAjax = $request->query->get('dateDebut');
        // convert date string to timestamp
        $timeDebut = strtotime($dateDebutAjax);
        $dateDebutStr = date('Y-m-d', $timeDebut);
        //convert a string to date php
        $dateDebut = new \DateTime($dateDebutStr);

        // avy any amin'ny request ity (AJAX)
        $dateFinAjax = $request->query->get('dateFin');
        // convert date string to timestamp
        $timeFin = strtotime($dateFinAjax);
        $dateFinStr = date('Y-m-d', $timeFin);
        //convert a string to date php
        $dateFin = new \DateTime($dateFinStr);
        // dd($this->reservationRepo->findReservationIncludeDates($dateDebut, $dateFin));

        $datas = array();
        foreach ($this->getVehiculesDispo($dateDebut, $dateFin) as $key => $vehicule) {
            $datas[$key]['marque'] = $vehicule->getMarque()->getLibelle();
            $datas[$key]['modele'] = $vehicule->getModele();
            $datas[$key]['immatriculation'] = $vehicule->getImmatriculation();
        }

        return new JsonResponse($datas);
    }
    public function getVehiculesDispo($dateDebut, $dateFin)
    {
        $vehicules = $this->vehiculeRepo->findAll();
        $reservations = $this->reservationRepo->findReservationIncludeDates($dateDebut, $dateFin);
        dd($reservations);

        $i = 0;
        $vehiculeDispo = [];

        // code pour vehicule avec reservation , mila manao condition ame tsy misy reservation mihitsy
        foreach ($vehicules as $vehicule) {
            foreach ($reservations as $reservation) {
                if ($vehicule == $reservation->getVehicule()) {
                    $i++;
                }
            }
            if ($i == 0) {
                $vehiculeDispo[] = $vehicule;
            }
            $i = 0;
        }
        return $vehiculeDispo;
    }

    /**
     * @Route("/", name="reservation_index", methods={"GET"})
     */
    public function index(ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $reservationRepository->findBy([], ["id" => "DESC"]), /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            50/*limit per page*/
        );
        return $this->render('reservation/index.html.twig', [
            'reservations' => $pagination,
        ]);
    }

    /**
     * @Route("/new", name="reservation_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('reservation_index');
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="reservation_show", methods={"GET"})
     */
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="reservation_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Reservation $reservation): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('reservation_index');
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="reservation_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Reservation $reservation): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('reservation_index');
    }
}
