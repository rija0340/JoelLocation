<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\StopSalesType;
use App\Service\DateHelper;
use App\Service\TarifsHelper;
use App\Repository\UserRepository;
use App\Repository\MarqueRepository;
use App\Repository\ModeleRepository;
use App\Repository\TarifsRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StopSalesController extends AbstractController
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
    
    public function __construct(EntityManagerInterface $em, MarqueRepository $marqueRepo, ModeleRepository $modeleRepo, TarifsHelper $tarifsHelper, DateHelper $dateHelper, TarifsRepository $tarifsRepo, ReservationRepository $reservationRepo,  UserRepository $userRepo, VehiculeRepository $vehiculeRepo, OptionsRepository $optionsRepo, GarantieRepository $garantiesRepo)
    {

        $this->reservationRepo = $reservationRepo;
        $this->vehiculeRepo = $vehiculeRepo;
        $this->optionsRepo = $optionsRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->userRepo = $userRepo;
        $this->tarifsRepo = $tarifsRepo;
        $this->dateHelper = $dateHelper;
        $this->tarifsHelper = $tarifsHelper;
        $this->modeleRepo = $modeleRepo;
        $this->marqueRepo = $marqueRepo;
        $this->em = $em;
    }
    /**
     * @Route("/backoffice/stop_sales", name="stop_sales", methods={"GET","POST"})
     */
    public function stop_sales(Request $request, ReservationRepository $reservationRepository,  UserRepository $userRepo,  VehiculeRepository $vehiculeRepository): Response
    {

        $listeStopSales = new Reservation();
        $reservation = new Reservation();

        $listeStopSales =  $reservationRepository->findStopSales();

        $super_admin = $this->getUser();

        $formStopSales = $this->createForm(StopSalesType::class, $reservation);

        $formStopSales->handleRequest($request);

        if ($formStopSales->isSubmitted() && $formStopSales->isValid()) {

            $vehicule = $vehiculeRepository->find($request->request->get('select'));
            $entityManager = $this->getDoctrine()->getManager();
            $reservation->setVehicule($vehicule);
            $reservation->setCodeReservation('stopSale');
            $reservation->setAgenceDepart('garage');
            $reservation->setArchived(false);
            $reservation->setClient($super_admin);
            $reservation->setDateReservation($this->dateHelper->dateNow());
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('stop_sales');
        }

        return $this->render('admin/stop_sales_vehicules/index.html.twig', [
            'listeStopSales' => $listeStopSales,
        ]);
    }
    /**
     * @Route("/backoffice/stopSalesNew", name="stopSalesNew", methods={"GET","POST"})
     */
    public function stopSalesNew(Request $request, VehiculeRepository $vehiculeRepository,  UserRepository $userRepo): Response
    {

        $super_admin = $this->getUser();
        $reservation = new Reservation();
        $formStopSales = $this->createForm(StopSalesType::class, $reservation);
        $formStopSales->handleRequest($request);

        if ($formStopSales->isSubmitted() && $formStopSales->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $reservation->setClient($super_admin);
            $reservation->setDateReservation(new \DateTime('NOW'));
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('stop_sales');
        }

        return $this->render('admin/stop_sales_vehicules/new.html.twig', [
            'formStopSales' => $formStopSales->createView(),

        ]);
    }

    /**
     * @Route("/backoffice/{id}/editStopSale", name="stopSale_edit", methods={"GET","POST"}, requirements={"id":"\d+"})
     */
    public function editStopSale(Request $request, Reservation $reservation, ReservationRepository $reservationRepository): Response
    {
        $listeStopSales =  $reservationRepository->findStopSales();

        $formStopSales = $this->createForm(StopSalesType::class, $reservation);
        $formStopSales->handleRequest($request);

        // dd($reservation);

        if ($formStopSales->isSubmitted() && $formStopSales->isValid()) {
            $vehicule = $this->vehiculeRepo->find($request->request->get('select'));
            $reservation->setVehicule($vehicule);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('stop_sales');
        }

        return $this->render('admin/stop_sales_vehicules/edit.html.twig', [
            'listeStopSales' => $listeStopSales,
            'formStopSales' => $formStopSales->createView(),
            'vehicule' => $reservation->getVehicule()
        ]);
    }

    /**
     * @Route("/backoffice/{id}/delete", name="stopSale_delete", methods={"DELETE"},requirements={"id":"\d+"})
     */
    public function stopSaleDelete(Request $request, Reservation $reservation): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('stop_sales');
    }
}
