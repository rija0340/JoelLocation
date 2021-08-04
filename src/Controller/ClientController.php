<?php

namespace App\Controller;

use App\Entity\Faq;
use App\Entity\User;
use App\Entity\Devis;
use App\Form\UserType;
use App\Form\LoginType;
use App\Entity\Paiement;
use App\Entity\Vehicule;
use App\Form\ClientType;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Entity\ModePaiement;
use App\Service\TarifsHelper;
use App\Entity\EtatReservation;
use App\Entity\ModeReservation;
use App\Repository\UserRepository;
use App\Form\ReservationclientType;
use App\Repository\DevisRepository;
use App\Repository\TarifsRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;
use App\Controller\ReservationController;
use App\Form\ClientEditType;
use App\Repository\ReservationRepository;
use App\Repository\EtatReservationRepository;
use App\Repository\ModeReservationRepository;
use DateTimeZone;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ClientController extends AbstractController
{
    private $passwordEncoder;
    private $vehiculeRepo;
    private $optionsRepo;
    private $garantiesRepo;
    private $reservRepo;
    private $devisRepo;
    private $tarifsHelper;
    private $dateHelper;
    private $reservController;
    private $userRepo;

    public function __construct(UserRepository $userRepo, ReservationController $reservController, DateHelper $dateHelper, TarifsHelper $tarifsHelper, DevisRepository $devisRepo, ReservationRepository $reservRepo, UserPasswordEncoderInterface $passwordEncoder, VehiculeRepository $vehiculeRepository, OptionsRepository $optionsRepo, GarantieRepository $garantiesRepo)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->vehiculeRepo = $vehiculeRepository;
        $this->optionsRepo = $optionsRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->reservRepo = $reservRepo;
        $this->devisRepo = $devisRepo;
        $this->tarifsHelper = $tarifsHelper;
        $this->dateHelper = $dateHelper;
        $this->reservController = $reservController;
        $this->userRepo = $userRepo;
    }


    /**
     * @Route("/espaceclient", name="espaceClient_index")
     */
    public function client(Request $request): Response
    {
        $client = $this->getUser();
        $date = new \DateTime('now');
        $message_reservation = '';
        $reservation = new Reservation();
        // $client1 = new User();
        $mode_reservation = $this->getDoctrine()->getRepository(ModeReservation::class)->findOneBy(['id' => 3]);
        $etat_reservation = $this->getDoctrine()->getRepository(EtatReservation::class)->findOneBy(['id' => 1]);
        $form = $this->createForm(ReservationclientType::class, $reservation);
        $formClient = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $reservation->setDateReservation($date);
            $reservation->setCodeReservation("123");
            $reservation->setClient($client);
            $reservation->setUtilisateur($client);
            $reservation->setModeReservation($mode_reservation);
            $reservation->setEtatReservation($etat_reservation);
            $entityManager->persist($reservation);
            $entityManager->flush();
            $message_reservation = 'reservation enregister avec sussès';
            return $this->redirectToRoute('carte');
        }

        // page client.html auparavant
        return $this->render('client/index.html.twig', [
            'controller_name' => 'AccueilController',
            'client' => $client->getUsername(),
            'id' => $client->getId(),
            'message' => $message_reservation,
            'form' => $form->createView(),
            'formClient' => $formClient->createView(),


        ]);
    }

    /** 
     * @Route("/espaceclient/reservations", name="client_reservations", methods={"GET","POST"})
     */
    public function listeReservations(Request $request): Response
    {
        $client = $this->getUser();
        $date = new \DateTime('now');


        //récupération des réservations effectuée
        $reservationEffectuers = $this->reservRepo->findReservationEffectuers($client, $date);

        //récupération des réservations en cours
        $reservationEncours = $this->reservRepo->findReservationEncours($client, $date);

        $res_attente_dateDebut = $this->reservRepo->findReservationsAttenteDateDebut($client, $date);

        //récupération des réservation en attente (devis envoyé et en attente de validation par client)
        // $reservationEnAttentes = $this->reservRepo->findReservationEnAttente($client, $date);
        $res_attente_validation = $this->devisRepo->findBy(['client' => $client, 'transformed' => false]);

        return $this->render('client/reservation/index.html.twig', [
            'reservation_effectuers' => $reservationEffectuers,
            'reservation_en_cours' => $reservationEncours,
            'res_attente_validation' => $res_attente_validation,
            'res_attente_dateDebut' => $res_attente_dateDebut,
        ]);
    }


    /** 
     * @Route("/espaceclient/new/reservation", name="client_nouvelleReserv", methods={"GET","POST"})
     */
    public function nouvelleReservation(Request $request): Response
    {


        //get options et garanties pour smart wizard
        $options = $this->optionsRepo->findAll();
        $garanties = $this->garantiesRepo->findAll();
        $client = $this->getUser();
        return $this->render('client/steps/index.html.twig', [
            'options' => $options,
            'garanties' => $garanties,
            'clientID' => $client->getId()
        ]);
    }


    /**
     * @Route("/espaceclient/reservationWizard", name="client_reserverWizard",  methods={"GET","POST"})
     */
    public function reserverWeb(Request $request): Response
    {
        $reservation = new Reservation();

        if ($request->isXmlHttpRequest()) {

            $clientID =  $request->query->get('clientID');
            $agenceDepart = $request->query->get('agenceDepart');
            $agenceRetour = $request->query->get('agenceRetour');
            $lieuSejour = $request->query->get('lieuSejour');
            $dateTimeDepart = $request->query->get('dateTimeDepart');
            $dateTimeRetour = $request->query->get('dateTimeRetour');
            $vehiculeIM = $request->query->get('vehiculeIM');
            $conducteur = $request->query->get('conducteur');
            $idSiege = $request->query->get('idSiege');
            $idGarantie = $request->query->get('idGarantie');

            $siege = $this->optionsRepo->find($idSiege);
            $garantie = $this->garantiesRepo->find($idGarantie);
            $vehicule = $this->vehiculeRepo->findByIM($vehiculeIM);
            $client = $this->userRepo->find($clientID);
            $duree = $this->dateHelper->calculDuree($dateTimeDepart, $dateTimeRetour);

            $reservation->setVehicule($vehicule);
            $reservation->setClient($client);
            $reservation->setAgenceDepart($agenceDepart);
            $reservation->setAgenceRetour($agenceRetour);
            $reservation->setDateDebut(new \DateTime($dateTimeDepart));
            $reservation->setDateFin(new \DateTime($dateTimeRetour));
            $reservation->setGarantie($garantie);
            $reservation->setSiege($siege);
            $reservation->setConducteur($conducteur);
            $reservation->setLieu($lieuSejour);
            $reservation->setDuree($duree);
            $reservation->setDateReservation($this->dateHelper->dateNow());
            $reservation->setCodeReservation($agenceDepart);
            // ajout reference dans Entity RESERVATION (CPTGP + year + month + ID)
            $lastID = $this->reservRepo->findBy(array(), array('id' => 'DESC'), 1);
            if ($lastID == null) {
                $currentID = 1;
            } else {
                $currentID = $lastID[0]->getId() + 1;
            }
            $pref = "WEB";
            $reservation->setRefRes($pref, $currentID);

            $prix = $this->tarifsHelper->calculTarif($dateTimeDepart, $dateTimeRetour, $siege, $garantie, $vehicule);

            $reservation->setPrix($prix);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('client_reservations');
        }
        return $this->redirectToRoute('client_reservations');
    }


    /**
     * @Route("/espaceclient/reserverDevis/{id}", name="client_reserverDevis", methods={"GET","POST"})
     */
    public function client_reserverDevis(Request $request, Devis $devis)
    {
        $reservation = new Reservation();
        $reservation->setVehicule($devis->getVehicule());
        $reservation->setClient($devis->getClient());
        $reservation->setDateDebut($devis->getDateDepart());
        $reservation->setDateFin($devis->getDateRetour());
        $reservation->setAgenceDepart($devis->getAgenceDepart());
        $reservation->setAgenceRetour($devis->getAgenceRetour());
        $reservation->setGarantie($devis->getGarantie());
        $reservation->setSiege($devis->getSiege());
        $reservation->setPrix($devis->getPrix());
        $reservation->setNumDevis($devis->getNumero());
        $reservation->setDateReservation($this->dateHelper->dateNow());
        $reservation->setCodeReservation('devisTransformé');
        // ajout reference dans Entity RESERVATION (CPTGP + year + month + ID)
        $lastID = $this->reservRepo->findBy(array(), array('id' => 'DESC'), 1);
        $currentID = $lastID[0]->getId() + 1;
        $reservation->setRefRes("WEB", $currentID);

        $devis->setTransformed(true);

        $entityManager = $this->reservController->getDoctrine()->getManager();
        $entityManager->persist($reservation);
        $entityManager->flush();

        return $this->redirectToRoute('client_reservations');
    }

    /**
     * @Route("/espaceclient/detailsVehicule", name="client_detailsVehicule", methods={"GET"})
     */
    public function detailsVehicule(VehiculeRepository $vehiculeRepository, Request $request)
    {
        $vehicule = new Vehicule;
        $id = intVal($request->query->get('vehicule_id'));
        $vehicule =  $vehiculeRepository->find($id);

        $data = array();

        $data['id'] = $vehicule->getId();
        $data['marque'] = $vehicule->getMarque()->getLibelle();
        $data['modele'] = $vehicule->getModele();
        $data['carburation'] = $vehicule->getCarburation();
        $data['vitesse'] = $vehicule->getVitesse();
        $data['immatriculation'] = $vehicule->getImmatriculation();
        $data['bagages'] = $vehicule->getBagages();
        $data['atouts'] = $vehicule->getAtouts();
        $data['caution'] = $vehicule->getCaution();
        $data['details'] = $vehicule->getDetails();
        $data['portes'] = $vehicule->getPortes();
        $data['passagers'] = $vehicule->getPassagers();
        $data['image'] = $vehicule->getImage();

        return new JsonResponse($data);
    }

    /**
     * @Route("/espaceclient/tarifsVehicule", name="client_tarifsVehicule", methods={"GET"})
     */
    public function tarifsVehicule(Request $request, VehiculeRepository $vehiculeRepo, TarifsRepository $tarifsRepo)
    {
        $vehicule_id = intVal($request->query->get('vehicule_id'));
        $mois = $request->query->get('mois');

        // dd($vehicule_id, $mois);
        // die();
        $vehicule = $vehiculeRepo->find($vehicule_id);
        $tarif =  $tarifsRepo->findTarifs($vehicule, $mois);

        $data = array();

        $data['troisJours'] = $tarif->getTroisJours();
        $data['septJours'] = $tarif->getSeptJours();
        $data['quinzeJours'] = $tarif->getQuinzeJours();
        $data['trenteJours'] = $tarif->getTrenteJours();


        return new JsonResponse($data);
    }

    /**
     * @Route("/espaceclient/listeOptions", name="client_listeOptions", methods={"GET"})
     */
    public function client_listeOptions(Request $request)
    {
        $data = array();
        $options = $this->optionsRepo->findAll();

        foreach ($options as $key => $option) {

            $data[$key]['id'] = $option->getId();
            $data[$key]['appelation'] = $option->getAppelation();
            $data[$key]['prix'] = $option->getPrix();
        }

        return new JsonResponse($data);
    }


    /**
     * @Route("/espaceclient/listeGaranties", name="client_listeGaranties", methods={"GET"})
     */
    public function client_listeGaranties(Request $request)
    {
        $data = array();
        $garanties = $this->garantiesRepo->findAll();

        foreach ($garanties as $key => $garantie) {

            $data[$key]['id'] = $garantie->getId();
            $data[$key]['appelation'] = $garantie->getAppelation();
            $data[$key]['prix'] = $garantie->getPrix();
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/espaceclient/modifier/{id}", name="infoclient_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(ClientEditType::class, $user);

        // dump($request);
        // die();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            ));
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('espaceClient_index');
        }

        return $this->render('client/information/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/inscription", name="inscription")
     */
    public function inscription(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(ClientType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_CLIENT']);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            ));
            $user->setPresence(1);
            $user->setDateInscription($this->dateHelper->dateNow());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }
        return $this->render('accueil/inscription.html.twig', [
            'controller_name' => 'InscriptionController',
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/login_client", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $user = new User();
        $form = $this->createForm(LoginType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$entityManager = $this->getDoctrine()->getManager();
            //$entityManager->persist($user);
            //$entityManager->flush();
            $userreq = new User();
            $username = $user->getUsername();
            $userpass = $this->passwordEncoder->encodePassword($user, $user->getPassword());
            $userreq = $this->getDoctrine()->getRepository(User::class)->findOneBy(['username' => $username, 'password' => $userpass]);
            if ($userreq != null) {
                //if($user->setPassword($this->passwordEncoder->encodePassword($user,$user->getPassword())) == $userreq->getPassword()){ 
                return $this->redirectToRoute('accueil');
                //}
            }
        }
        return $this->render('accueil/login.html.twig', [
            'controller_name' => 'LoginController',
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/payement", name="payement", methods={"GET","POST"})
     */
    public function payement(Request $request)
    {
        $client = $this->getUser();
        $reservation = $this->getDoctrine()->getRepository(Reservation::class)->findOneBy(["client" => $client], ["id" => "DESC"]);
        $modePaiement = $this->getDoctrine()->getRepository(ModePaiement::class)->findOneBy(["id" => 1]);
        $vehicule = new Vehicule();
        if($reservation == null){            
            return $this->redirectToRoute('client');
        }
        $vehicule = $reservation->getVehicule();
        $caution = $vehicule->getCaution() * 100;
        // Set your secret key. Remember to switch to your live secret key in production.
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey('sk_test_51INCSpLWsPgEVX5UZKrH0YIs7H7PF8Boao1VcYHEks40it5a39h5KJzcwWxSWUIV6ODWkPS7txKsRyKeSfBknDFC00PAHEBwVP');

        // Token is created using Stripe Checkout or Elements!
        // Get the payment token ID submitted by the form:
        //$token = $_POST['stripeToken'];
        $token = $request->request->get('stripeToken');
        $charge = \Stripe\Charge::create([
            'amount' => $caution,
            'currency' => 'eur',
            'source' => $token,
            'description' => 'caution pour le véhicule ' . $vehicule->getMarque() . ' ' . $vehicule->getModele(),
        ]);

        $paiement = new Paiement();
        $paiement->setReservation($reservation);
        $paiement->setModePaiement($modePaiement);
        $paiement->setUtilisateur($client);
        $paiement->setClient($client);
        $paiement->setMontant($vehicule->getCaution());
        $paiement->setDatePaiement($this->dateHelper->dateNow());
        $paiement->setMotif('caution pour le véhicule ' . $vehicule->getMarque() . ' ' . $vehicule->getModele());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($paiement);
        $entityManager->flush();
        //return $this->redirectToRoute('espaceClient_index');
        return $this->redirectToRoute('client');
    }
}
