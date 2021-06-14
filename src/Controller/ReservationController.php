<?php

namespace App\Controller;

use App\Entity\Vehicule;
use App\Form\VehiculeType;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Form\StopSalesType;
use App\Entity\User;
use App\Form\UserType;
use App\Form\UserClientType;
use App\Repository\UserRepository;
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

        $dateDebutday = $request->query->get('dateDebutday');
        $dateDebutmonth = $request->query->get('dateDebutmonth');
        $dateDebutyear = $request->query->get('dateDebutyear');
        $dateDebuthours = $request->query->get('dateDebuthours');
        $dateDebutminutes = $request->query->get('dateDebutminutes');
        $dateFinday = $request->query->get('dateFinday');
        $dateFinmonth = $request->query->get('dateFinmonth');
        $dateFinyear = $request->query->get('dateFinyear');
        $dateFinhours = $request->query->get('dateFinhours');
        $dateFinminutes = $request->query->get('dateFinminutes');

        $dateDebut = date("Y-m-d H:i", mktime($dateDebuthours, $dateDebutminutes, 00, $dateDebutmonth, $dateDebutday, $dateDebutyear));
        $dateFin = date("Y-m-d H:i", mktime($dateFinhours, $dateFinminutes, 00, $dateFinmonth, $dateFinday, $dateFinyear));


        $dateDebut = new \DateTime($dateDebut);

        $dateFin = new \DateTime($dateFin);


        $datas = array();
        foreach ($this->getVehiculesDispo($dateDebut, $dateFin) as $key => $vehicule) {
            $datas[$key]['id'] = $vehicule->getId();
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
        // dd($reservations);

        $i = 0;
        $vehiculeDispo = [];

        // code pour vehicule avec reservation , mila manao condition amle tsy misy reservation mihitsy
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

        $reservations = $reservationRepository->findBy([], ["id" => "DESC"]);

        $pagination = $paginator->paginate(
            $reservations, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            50/*limit per page*/
        );
        return $this->render('admin/reservation/crud/index.html.twig', [
            'reservations' => $pagination,
        ]);
    }

    /**
     * @Route("/new", name="reservation_new", methods={"GET","POST"})
     */
    public function new(Request $request, VehiculeRepository $vehiculeRepository): Response
    {

        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $vehicule = $vehiculeRepository->find($request->request->get('select'));
            $entityManager = $this->getDoctrine()->getManager();
            $reservation->setVehicule($vehicule);
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('reservation_index');
        }

        return $this->render('admin/reservation/crud/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/stop_sales", name="stop_sales", methods={"GET","POST"})
     */
    public function stop_sales(Request $request, ReservationRepository $reservationRepository, UserRepository $userRepo): Response
    {
        $listeStopSales =  $reservationRepository->findStopSales();

        $super_admin = $userRepo->findSuperAdmin();
        $reservation = new Reservation();
        $formStopSales = $this->createForm(StopSalesType::class, $reservation);
        $formStopSales->handleRequest($request);

        if ($formStopSales->isSubmitted() && $formStopSales->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $reservation->setClient($super_admin);
            $reservation->setDateReservation(new \DateTime('NOW'));
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('reservation_index');
        }

        return $this->render('admin/stop_sales_vehicules/index.html.twig', [
            'listeStopSales' => $listeStopSales,
            'formStopSales' => $formStopSales->createView(),
        ]);
    }


    /**
     * @Route("/{id}", name="reservation_show", methods={"GET"})
     */
    public function show(Reservation $reservation): Response
    {
        return $this->render('admin/reservation/crud/show.html.twig', [
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
            $vehicule = $this->vehiculeRepo->find($request->request->get('select'));
            $reservation->setVehicule($vehicule);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('reservation_index');
        }

        return $this->render('admin/reservation/crud/edit.html.twig', [
            'reservation' => $reservation,
            'imVeh' => $reservation->getVehicule()->getImmatriculation(), //utile pour val par défaut select
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

    /**
     * @Route("/recherchesimple", name="recherche_simple", methods={"GET", "POST"})
     */
    public function rechercheSimple(Request $request, UserRepository $userRepository, ReservationRepository $reservationRepository): Response
    {
        $recherche = $request->request->get('recherche');
        $reservation[] = new Reservation();
        if($recherche != null){ 
            $client_id = (int)$recherche;
            $client = new User();
            $reservation[] = new Reservation();
            //if($client_id){
                $client = $userRepository->findOneBy(["id" => $client_id]);
            //}
            if($client == null){
                $client = $userRepository->findOneBy(["username" => $recherche]);
            }
            if($client != null){
                $reservation = $reservationRepository->findBy(["client" => $client]);
            }else{
                $reservation = $reservationRepository->findBy(["code_reservation" => $recherche]);
            }
            return $this->redirectToRoute('rechercher_res');
        }
        return $this->redirectToRoute('reservation_index');
    }

    /**
     * @Route("/rechercheimmatriculation", name="recherche_immatriculation", methods={"GET", "POST"})
     */
    public function rechercheImmatriculaiton(Request $request, UserRepository $userRepository, ReservationRepository $reservationRepository, VehiculeRepository $vehiculeRepository): Response
    {
        $immatriculation = $request->request->get('immatriculation');
        $reservation[] = new Reservation();
        if($immatriculation != null){ 
            $vehicule = $vehiculeRepository->findOneBy(["immatriculation" => $immatriculation]);
            $reservation = $reservationRepository->findBy(["vehicule" => $vehicule]);
        }
        return $this->redirectToRoute('reservation_index');
    }
}
