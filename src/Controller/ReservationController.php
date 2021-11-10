<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Classe\Mailjet;
use App\Entity\User;
use App\Form\UserType;
use App\Entity\Garantie;
use App\Entity\Vehicule;
use App\Entity\InfosResa;
use App\Entity\Conducteur;
use App\Form\VehiculeType;
use App\Entity\Reservation;
use App\Form\InfosResaType;
use App\Form\StopSalesType;
use App\Service\DateHelper;
use App\Entity\InfosVolResa;
use App\Form\ClientEditType;
use App\Form\ConducteurType;
use App\Form\UserClientType;
use App\Form\KilometrageType;
use App\Form\ReservationType;
use App\Service\TarifsHelper;
use App\Form\InfosVolResaType;
use App\Form\EditStopSalesType;
use App\Classe\ClasseReservation;
use App\Form\OptionsGarantiesType;
use App\Repository\UserRepository;
use App\Repository\MarqueRepository;
use App\Repository\ModeleRepository;
use App\Repository\TarifsRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;
use App\Form\EditClientReservationType;
use App\Repository\ConducteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Knp\Snappy\Pdf;
use Dompdf\Dompdf;
use Dompdf\Options;


/**
 * @Route("/reservation")
 */
class ReservationController extends AbstractController
{
    private $userRepo;
    private $conducteurRepo;
    private $router;
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

    public function __construct(ConducteurRepository $conducteurRepo, RouterInterface $router, FlashyNotifier $flashy, Mailjet $mail, EntityManagerInterface $em, MarqueRepository $marqueRepo, ModeleRepository $modeleRepo, TarifsHelper $tarifsHelper, DateHelper $dateHelper, TarifsRepository $tarifsRepo, ReservationRepository $reservationRepo,  UserRepository $userRepo, VehiculeRepository $vehiculeRepo, OptionsRepository $optionsRepo, GarantieRepository $garantiesRepo)
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
        $this->router = $router;
        $this->conducteurRepo = $conducteurRepo;
    }

    /**
     * @Route("/", name="reservation_index", methods={"GET"},requirements={"id":"\d+"})
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
     * @Route("/new", name="reservation_new", methods={"GET","POST"},requirements={"id":"\d+"})
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
     * @Route("/details/{id}", name="reservation_show", methods={"GET", "POST"},requirements={"id":"\d+"})
     */
    public function show(Reservation $reservation, Request $request): Response
    {

        $vehicule = $reservation->getVehicule();
        $formKM = $this->createForm(KilometrageType::class, $vehicule);
        $formKM->handleRequest($request);

        //extraction d'un conducteur parmi les conducteurs du client
        $conducteurs =  $reservation->getConducteursClient();
        $conducteur = $conducteurs[0];

        if ($formKM->isSubmitted() && $formKM->isValid()) {
            //sauvegarde données de kilométrage du véhicule
            $this->em->persist($vehicule);
            $this->em->flush();

            // notification pour réussite enregistrement
            $this->flashy->success("Les kilométrages sont bien enregistrés");
            return $this->render('admin/reservation/crud/show.html.twig', [
                'reservation' => $reservation,
                'conducteur' => $conducteur,
                'formKM' => $formKM->createView(),
            ]);
        }
        return $this->render('admin/reservation/crud/show.html.twig', [
            'reservation' => $reservation,
            'conducteur' => $conducteur,
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
            'routeReferer' => $this->getRouteForRedirection($reservation)
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
     * @Route("/archiver/{id}", name="reservation_archive", methods={"GET", "POST"},requirements={"id":"\d+"})
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
     *  @Route("/modifier/options-garanties/{id}", name="optionsGaranties_edit", methods={"GET","POST"},requirements={"id":"\d+"})
     */
    public function editOptionsGaranties(Request $request, Reservation $reservation): Response
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
            'options' => $options,
            'routeReferer' => $this->getRouteForRedirection($reservation)

        ]);
    }


    /**
     * @Route("/envoi-identification-connexion/{id}", name="reservation_ident_connex", methods={"GET","POST"},requirements={"id":"\d+"})
     */
    public function envoyerIdentConnex(Request $request, Reservation $reservation): Response
    {

        $mail = $reservation->getClient()->getMail();
        $nom = $reservation->getClient()->getNom();
        $mdp = uniqid();
        $content = "Bonjour, voici vos identifications de connexion. Mot de passe: " . $mdp;

        $this->mail->send($mail, $nom, "Identifiants de connexion", $content);

        $this->flashy->success("Vos identifians ont été envoyés");
        return $this->redirectToRoute($this->getReferer($request), ['id' => $reservation->getId()]);
    }

    /**
     *  @Route("/modifier/{id}/infos-client/", name="reservation_infosClient_edit", methods={"GET","POST"},requirements={"id":"\d+"})
     *
     */
    public function editInfosClient(Request $request, Reservation $reservation): Response
    {
        //form pour client
        $client = $this->reservationRepo->find($reservation->getId())->getClient();
        $form = $this->createForm(EditClientReservationType::class, $client);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($client);
            $this->em->flush();

            $this->flashy->success("La réservation a bien été modifié");
            return $this->redirectToRoute($this->getRouteForRedirection($reservation), ['id' => $reservation->getId()]);
        }

        return $this->render('admin/reservation/crud/infos_client/edit.html.twig', [

            'form' => $form->createView(),
            'reservation' => $reservation,
            'routeReferer' => $this->getRouteForRedirection($reservation)

        ]);
    }

    /**
     *  @Route("/ajouter-conducteur/", name="add_conducteur", methods={"GET","POST"},requirements={"id":"\d+"})
     *
     */
    public function addConducteur(Request $request): Response
    {
        if ($request->query->get('id_reservation') != null) {

            $id = $request->query->get('id_reservation');
            $reservation = $this->reservationRepo->find($id);
        } else {
            $reservation = null;
        }

        $conducteur  = new Conducteur();
        $form = $this->createForm(ConducteurType::class, $conducteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isSubmitted()) {

            // ajouter un conducteur
            $this->em->persist($conducteur);
            $this->em->flush();

            // ajouter conducteur à une réservation
            $reservation->addConducteursClient($conducteur);
            $this->em->flush();

            //notification succes
            $this->flashy->success('Le conducteur a bienc été ajouté');
            return $this->redirectToRoute($this->getRouteForRedirection($reservation), ['id' => $id]);
        }
        return $this->render('admin/reservation/crud/conducteur/new.html.twig', [

            'form' => $form->createView(),
            'refReservation' => $reservation->getReference(),
            'reservation' => $reservation,
            'routeReferer' => $this->getRouteForRedirection($reservation)
        ]);
    }

    /**
     * from autocompletion input
     *  @Route("/ajouter-conducteur-selection/", name="add_selected_conducteur", methods={"GET","POST"},requirements={"id":"\d+"})
     *
     */
    public function addSelectedConducteur(Request $request)
    {
        //extracion mail from string format : "nom prenom (mail)"
        $conducteur = $request->request->get('selectedConducteur');
        $conducteur = explode('(', $conducteur);
        $numPermis = explode(')', $conducteur[1]);
        $numeroPermis = $numPermis[0];

        $conducteur =  $this->conducteurRepo->findOneBy(['numeroPermis' => $numeroPermis]);
        $reservation = $this->reservationRepo->find($request->request->get('idReservation'));

        $reservation->addConducteursClient($conducteur);
        $this->em->flush();

        $this->flashy->success("Le conducteur a été ajouté aved succès");
        return $this->redirectToRoute($this->getRouteForRedirection($reservation), ['id' => $reservation->getId()]);
    }


    /**
     *  @Route("/liste-conducteurs/", name="liste_conducteurs", methods={"GET","POST"},requirements={"id":"\d+"})
     *
     */
    public function listeConduteurs(Request $request): Response
    {
        $id = $request->query->get('idReservation');
        $reservation = $this->reservationRepo->find($id);
        $conducteurs = $this->conducteurRepo->findBy(['client' => $reservation->getClient(), 'reservation' => null]);

        $data = array();
        foreach ($conducteurs as $key => $conducteur) {

            $data[$key]['nom'] = $conducteur->getNom();
            $data[$key]['prenom'] = $conducteur->getNom();
            $data[$key]['numPermis'] = $conducteur->getNumeroPermis();
        }


        return new JsonResponse($data);
    }

    //return route en fonction date (comparaison avec dateNow pour savoir statut réservation)
    public function getRouteForRedirection($reservation)
    {

        $dateDepart = $reservation->getDateDebut();
        $dateRetour = $reservation->getDateFin();
        $dateNow = $this->dateHelper->dateNow();

        //classement des réservations

        // 1-nouvelle réservation -> dateNow > dateReservation
        if ($dateNow > $dateDepart) {
            $routeReferer = 'reservation_show';
        }
        if ($dateDepart < $dateNow && $dateNow < $dateRetour) {
            $routeReferer = 'contrats_show';
        }
        if ($dateNow > $dateRetour) {
            $routeReferer = 'contrat_termine_show';
        }
        return $routeReferer;
    }

    //return referer->route avant la rédirection (source)
    public function getReferer($request)
    {
        // get the referer, it can be empty!
        $referer = $request->headers->get('referer');
        if (!\is_string($referer) || !$referer) {
        }

        if ($referer != null) {
            $refererPathInfo = Request::create($referer)->getPathInfo();

            // try to match the path with the application routing
            $routeInfos = $this->router->match($refererPathInfo);

            // get the Symfony route name if it exists
            $refererRoute = $routeInfos['_route'] ?? '';

            return $refererRoute;
        }
    }
}
