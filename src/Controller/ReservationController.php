<?php

namespace App\Controller;

use App\Classe\ClasseReservation;
use App\Classe\Mail;
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
use App\Form\OptionsGarantiesType;
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
use MercurySeries\FlashyBundle\FlashyNotifier;
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
    private $mail;
    private $flashy;

    public function __construct(FlashyNotifier $flashy, Mail $mail, EntityManagerInterface $em, MarqueRepository $marqueRepo, ModeleRepository $modeleRepo, TarifsHelper $tarifsHelper, DateHelper $dateHelper, TarifsRepository $tarifsRepo, ReservationRepository $reservationRepo,  UserRepository $userRepo, VehiculeRepository $vehiculeRepo, OptionsRepository $optionsRepo, GarantieRepository $garantiesRepo)
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
        $this->mail = $mail;
        $this->flashy = $flashy;
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
            'reservations' => $reservations,
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
            ]);
        }
        return $this->render('admin/reservation/crud/show.html.twig', [
            'reservation' => $reservation,
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

    /**
     * @Route("/archiver/{id}", name="reservation_archive", methods={"GET", "POST"})
     */
    public function archiver(Request $request, Reservation $reservation): Response
    {
        $reservation->setArchived(true);
        $this->em->flush();

        // dd($reservation);
        $this->flashy->success("La réservation N° " . $reservation->getReference() . " a été archivée");
        return $this->redirectToRoute('reservation_index');
    }


    /**
     *  @Route("/modifier/options-garanties/{id}", name="optionsGaranties_edit", methods={"GET","POST"})
     */
    public function optionsGarantiesEdit(Request $request, Reservation $reservation): Response
    {

        $form = $this->createForm(OptionsGarantiesType::class, $reservation);
        $garanties = $this->garantiesRepo->findAll();
        $options = $this->optionsRepo->findAll();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reservation->setPrixGaranties($reservation->getSommeGaranties());
            $reservation->setPrixOptions($reservation->getSommeOptions());
            $reservation->setPrix($reservation->getTarifVehicule() + $reservation->getPrixGaranties() + $reservation->getPrixOptions());
            $this->em->flush();
            return $this->redirectToRoute('reservation_show', ['id' => $reservation->getId()]);
        }
        return $this->render('admin/reservation/crud/options_garanties/edit.html.twig', [
            'form' => $form->createView(),
            'reservation' => $reservation,
            'garanties' => $garanties,
            'options' => $options
        ]);
    }

    /**
     *  @Route("/envoi/identifiants-connexion/{id}", name="envoiIdentifiantsConnexion", methods={"GET","POST"})
     */
    public function EnvoiIdentifiantsConnexion(Request $request, Reservation $reservation): Response
    {

        $email_adress = $reservation->getClient()->getMail();
        $name = $reservation->getClient()->getNom();
        $subject =  "Indentifiants de connexion";
        $content = "Bonjour, voici vos identifiants de connexion. Login : " . $email_adress . ". Mot de passe : 0000. Veuillez changer votre mot de passe le plus tôt possible";

        $this->mail->send($email_adress, $name, $subject, $content);

        $this->flashy->success("Les identifiants de connexion ont été envoyé au client");
        return $this->redirectToRoute('reseration_show', ['id' => $reservation->getId()]);
    }
}
