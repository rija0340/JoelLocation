<?php

namespace App\Controller;

use App\Classe\ClasseReservation;
use DateTime;
use DateTimeZone;
use App\Entity\User;
use App\Form\UserType;
use App\Entity\Garantie;
use App\Entity\Vehicule;
use App\Form\VehiculeType;
use App\Entity\Reservation;
use App\Form\StopSalesType;
use App\Service\DateHelper;
use App\Form\UserClientType;
use App\Form\KilometrageType;
use App\Form\ReservationType;
use App\Service\TarifsHelper;
use App\Form\EditStopSalesType;
use App\Repository\UserRepository;
use App\Repository\TarifsRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\MarqueRepository;
use App\Repository\ModeleRepository;
use App\Repository\VehiculeRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        $this->tarifsRepo = $tarifsRepo;
        $this->userRepo = $userRepo;
        $this->dateHelper = $dateHelper;
        $this->tarifsHelper = $tarifsHelper;
        $this->modeleRepo = $modeleRepo;
        $this->marqueRepo = $marqueRepo;
        $this->em = $em;
    }

    /**
     * @Route("/", name="reservation_index", methods={"GET"})
     */
    public function index(ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
    {

        $reservations = $reservationRepository->findReservationsSansStopSales();

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
     * @Route("/newReservation", name="reserverVenteComptoir",  methods={"GET","POST"})
     */
    public function reserverVenteComptoir(Request $request): Response
    {

        if ($request->isXmlHttpRequest()) {

            $reservation = new Reservation();

            $options = [];
            $garanties = [];

            $idClient =  $request->query->get('idClient');
            $agenceDepart = $request->query->get('agenceDepart');
            $agenceRetour = $request->query->get('agenceRetour');
            $lieuSejour = $request->query->get('lieuSejour');
            $dateTimeDepart = $request->query->get('dateTimeDepart');
            $dateTimeRetour = $request->query->get('dateTimeRetour');
            $vehiculeIM = $request->query->get('vehiculeIM');
            $conducteur = $request->query->get('conducteur');
            $arrayOptionsID = (array) $request->query->get('arrayOptionsID');
            $arrayGarantiesID = (array)$request->query->get('arrayGarantiesID');

            $dateDepart = new \DateTime($dateTimeDepart);
            $dateRetour = new \DateTime($dateTimeRetour);

            $vehicule = $this->vehiculeRepo->findByIM($vehiculeIM);
            $client = $this->userRepo->find($idClient);
            $duree = $this->dateHelper->calculDuree($dateDepart, $dateRetour);
            $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);

            $reservation->setVehicule($vehicule);
            $reservation->setClient($client);
            $reservation->setAgenceDepart($agenceDepart);
            $reservation->setAgenceRetour($agenceRetour);
            $reservation->setDateDebut($dateDepart);
            $reservation->setDateFin($dateRetour);
            //loop sur id des options
            if ($arrayOptionsID != []) {
                for ($i = 0; $i < count($arrayOptionsID); $i++) {

                    $id = $arrayOptionsID[$i];
                    $option = $this->optionsRepo->find($id);
                    array_push($options, $option);
                    $reservation->addOption($option);
                }
            }

            //loop sur id des garanties
            if ($arrayOptionsID != []) {

                for ($i = 0; $i < count($arrayGarantiesID); $i++) {

                    $id = $arrayGarantiesID[$i];
                    $garantie = $this->garantiesRepo->find($id);
                    array_push($garanties, $garantie);
                    $reservation->addGaranty($garantie);
                }
            }

            $reservation->setConducteur($conducteur);
            $reservation->setLieu($lieuSejour);
            $reservation->setDuree($duree);
            $reservation->setDateReservation($this->dateHelper->dateNow());
            $reservation->setCodeReservation($agenceDepart);
            $reservation->setTarifVehicule($tarifVehicule);
            // ajout reference dans Entity RESERVATION (CPTGP + year + month + ID)

            $lastID = $this->reservationRepo->findBy(array(), array('id' => 'DESC'), 1);
            if ($lastID == null) {
                $currentID = 1;
            } else {
                $currentID = $lastID[0]->getId() + 1;
            }
            $pref = "CPT";
            $reservation->setRefRes($pref, $currentID);

            $prix = $this->tarifsHelper->calculTarifTotal($tarifVehicule, $options, $garanties);

            $reservation->setPrix($prix);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('reservation_index');
        }
        return $this->redirectToRoute('reservation_index');
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

            $vehicule = $this->vehiculeRepo->find($request->request->get('select'));
            $entityManager = $this->getDoctrine()->getManager();
            $reservation->setVehicule($vehicule);

            // ajout reference dans Entity RESERVATION (CPT + year + month + ID)

            $lastID = $this->reservationRepo->findBy(array(), array('id' => 'DESC'), 1);
            if ($lastID == null) {
                $currentID = 1;
            } else {
                $currentID = $lastID[0]->getId() + 1;
            }
            $pref = "CPT";
            $reservation->setRefRes($pref, $currentID);

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
     * @Route("/show/{id}", name="reservation_show", methods={"GET"},requirements={"id":"\d+"})
     */
    public function show(Reservation $reservation, Request $request): Response
    {
        // return $this->render('admin/reservation/crud/show.html.twig', [
        //     'reservation' => $reservation,
        // ]);

        $formKM = $this->createForm(KilometrageType::class, $reservation);
        $formKM->handleRequest($request);

        if ($formKM->isSubmitted() && $formKM->isValid()) {

            $entityManager = $this->reservController->getDoctrine()->getManager();
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->render('admin/reservation/crud/show.html.twig', [
                'reservation' => $reservation,

                'formKM' => $formKM->createView(),
                'tarifVehicule' => $this->tarifsHelper->calculTarifVehicule($reservation->getDateDebut(), $reservation->getDateFin(), $reservation->getVehicule()),
                'tarifOptions' => $this->tarifsHelper->sommeTarifsOptions($reservation->getOptions()),
                'tarifGaranties' => $this->tarifsHelper->sommeTarifsGaranties($reservation->getGaranties()),
            ]);
        }

        return $this->render('admin/reservation/crud/show.html.twig', [
            'reservation' => $reservation,
            'tarifVehicule' => $this->tarifsHelper->calculTarifVehicule($reservation->getDateDebut(), $reservation->getDateFin(), $reservation->getVehicule()),
            'tarifOptions' => $this->tarifsHelper->sommeTarifsOptions($reservation->getOptions()),
            'tarifGaranties' => $this->tarifsHelper->sommeTarifsGaranties($reservation->getGaranties()),
            'formKM' => $formKM->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="reservation_edit", methods={"GET","POST"},requirements={"id":"\d+"})
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
     * @Route("/{id}", name="reservation_delete", methods={"DELETE"},requirements={"id":"\d+"})
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
