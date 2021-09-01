<?php

namespace App\Controller;

use DateTimeZone;
use App\Entity\Faq;
use App\Entity\User;
use App\Entity\Devis;
use App\Form\UserType;
use App\Form\DevisType;
use App\Form\LoginType;
use App\Entity\Paiement;
use App\Entity\Vehicule;
use App\Form\ClientType;
use App\Entity\Conducteur;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Entity\ModePaiement;
use App\Form\ClientEditType;
use App\Form\ClientInfoType;
use App\Form\ConducteurType;
use App\Form\DevisClientType;
use App\Service\TarifsHelper;
use App\Form\ClientCompteType;
use App\Entity\EtatReservation;
use App\Entity\ModeReservation;
use App\Repository\UserRepository;
use App\Form\ReservationclientType;
use App\Repository\DevisRepository;
use App\Repository\TarifsRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;
use App\Repository\ConducteurRepository;
use App\Controller\ReservationController;
use App\Repository\ReservationRepository;
use App\Repository\EtatReservationRepository;
use App\Repository\ModeReservationRepository;
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
    private $conductRepo;

    public function __construct(ConducteurRepository $conductRepo,  UserRepository $userRepo, ReservationController $reservController, DateHelper $dateHelper, TarifsHelper $tarifsHelper, DevisRepository $devisRepo, ReservationRepository $reservRepo, UserPasswordEncoderInterface $passwordEncoder, VehiculeRepository $vehiculeRepository, OptionsRepository $optionsRepo, GarantieRepository $garantiesRepo)
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
        $this->conductRepo = $conductRepo;
    }


    /**
     * @Route("/espaceclient", name="espaceClient_index")
     */
    public function client(Request $request): Response
    {
        $client = $this->getUser();

        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }
        // dump($request);
        // die();
        $date = new \DateTime('now');
        $message_reservation = '';
        $reservation = new Reservation();
        // $client1 = new User();
        $mode_reservation = $this->getDoctrine()->getRepository(ModeReservation::class)->findOneBy(['id' => 3]);
        $etat_reservation = $this->getDoctrine()->getRepository(EtatReservation::class)->findOneBy(['id' => 1]);
        // $form = $this->createForm(ReservationclientType::class, $reservation);
        $formClient = $this->createForm(ClientType::class, $client);
        $formClientCompte = $this->createForm(ClientCompteType::class);
        // $form->handleRequest($request);
        $formClientCompte->handleRequest($request);

        if ($formClientCompte->isSubmitted() && $formClientCompte->isValid()) {

            if ($client->getPassword() == '') {
                $client->setPassword($client->getRecupass());
            } else {
                $client->setPassword($this->passwordEncoder->encodePassword(
                    $client,
                    $client->getPassword()
                ));
                $client->setRecupass($client->getPassword());
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reservation);
            $entityManager->flush();
            return $this->redirectToRoute('app_login');
        }

        // page client.html auparavant
        return $this->render('client/index.html.twig', [

            'client' => $client->getUsername(),
            'id' => $client->getId(),
            // 'form' => $form->createView(),
            'formClient' => $formClient->createView(),
            'formClientCompte' => $formClientCompte->createView(),

        ]);
    }

    //*********************MES CONDUCTEURS*********************** */


    /** 
     * @Route("/espaceclient/mesConducteurs", name="client_mesConducteurs", methods={"GET","POST"})
     */
    public function mesConducteurs(Request $request): Response
    {
        $client = $this->getUser();

        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }
        $conducteurs = $this->conductRepo->findBy(['client' => $client]);

        // $formClient = $this->createForm(ClientType::class, $client);

        return $this->render('client/conducteur/index.html.twig', [

            'client' => $client,
            'conducteurs' => $conducteurs
        ]);
    }

    /** 
     * @Route("/espaceclient/conducteur/new/", name="client_newConducteur", methods={"GET","POST"})
     */
    public function newConducteurs(Request $request): Response
    {
        $conducteur = new Conducteur();
        $client = $this->getUser();

        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }
        $formConducteur = $this->createForm(ConducteurType::class, $conducteur);
        $formConducteur->handleRequest($request);

        if ($formConducteur->isSubmitted() && $formConducteur->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $conducteur->setClient($client);
            $entityManager->persist($conducteur);
            $entityManager->flush();

            return $this->redirectToRoute('client_mesConducteurs');
        }

        return $this->render('client/conducteur/new.html.twig', [

            'formConducteur' => $formConducteur->createView()

        ]);
    }


    /**
     * @Route("/espaceclient/modifier/conducteur/{id}", name="conducteur_edit", methods={"GET","POST"})
     */
    public function editConducteur(Request $request, Conducteur $conducteur): Response
    {
        $formConducteur = $this->createForm(ConducteurType::class, $conducteur);
        $formConducteur->handleRequest($request);
        if ($formConducteur->isSubmitted() && $formConducteur->isValid()) {

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('client_mesConducteurs');
        }

        return $this->render('client/conducteur/edit.html.twig', [
            'formConducteur' => $formConducteur->createView(),
        ]);
    }
    //*******************fin mes conducteurs***************** */


    //***********************processus validation devis***************** */
    /**
     * @Route("/espaceclient/optionsGaranties", name="step2OptionsGaranties", methods={"GET","POST"})
     */
    public function step2OptionsGaranties(Request $request): Response
    {

        $devisID = $request->request->get('reservID');

        if ($devisID == null) {
            $devisID = $request->request->get('devisID');
        }

        $devis = $this->devisRepo->find($devisID);

        $garanties = $this->garantiesRepo->findAll();
        $options = $this->optionsRepo->findAll();

        $form = $this->createForm(DevisClientType::class, $devis);
        $form->handleRequest($request);

        $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($devis->getDateDepart(), $devis->getDateRetour(), $devis->getVehicule());

        $tarifTotal = $this->tarifsHelper->calculTarifTotal($tarifVehicule, $devis->getOptions(), $devis->getGaranties());

        $tarifJournalier = $tarifTotal / $this->dateHelper->calculDuree($devis->getDateDepart(), $devis->getDateRetour());

        $tarifJournalier = round($tarifJournalier, 2);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($devis);
            $entityManager->flush();

            return $this->redirectToRoute('step3infosClient', ['devisID' => $devisID]);
        }

        return $this->render('client/reservation/validation/step2OptionsGaranties.html.twig', [

            'garanties' => $garanties,
            'options' => $options,
            'devis' => $devis,
            'tarifTotal' => $tarifTotal,
            'tarifJournalier' => $tarifJournalier,
            'form' => $form->createView(),
            'devisID' => $devisID
        ]);
    }


    /**
     * @Route("/espaceclient/infosClient/{devisID}", name="step3infosClient", methods={"GET","POST"})
     */
    public function step3infosClient(Request $request, $devisID): Response
    {

        $client = $this->getUser();
        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }
        $listeDevis = $this->devisRepo->findBy(['client' => $client]);
        $devis = $this->devisRepo->find($devisID);

        $formClient = $this->createForm(ClientInfoType::class, $client);
        $formClient->handleRequest($request);

        $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($devis->getDateDepart(), $devis->getDateRetour(), $devis->getVehicule());

        $tarifTotal = $this->tarifsHelper->calculTarifTotal($tarifVehicule, $devis->getOptions(), $devis->getGaranties());

        $tarifJournalier = $tarifTotal / $this->dateHelper->calculDuree($devis->getDateDepart(), $devis->getDateRetour());

        $tarifJournalier = round($tarifJournalier, 2);

        $vingtPourcentTarifTotal = (20 * $tarifTotal) / 100;

        $cinquantePourcentTarifTotal = (50 * $tarifTotal) / 100;

        if ($formClient->isSubmitted() && $formClient->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($client);
            $entityManager->flush();

            $sommePaiement = $request->request->get('modePaiement');

            return $this->redirectToRoute('step4paiement', ['devisID' => $devisID, 'sommePaiement' => $sommePaiement]);
        }

        if ($devis->getClient() == $client) {
            return $this->render('client/reservation/validation/step3infosClient.html.twig', [
                'devis' => $devis,
                'tarif' => $tarifVehicule,
                'tarifJournalier' => $tarifJournalier,
                'vingtPourcentTarifTotal' => $vingtPourcentTarifTotal,
                'cinquantePourcentTarifTotal' => $cinquantePourcentTarifTotal,
                'devis' => $devis,
                'tarifTotal' => $tarifTotal,
                'formClient' => $formClient->createView()
            ]);
        } else {
            return $this->render('client/reservation/validation/error.html.twig');
        }
    }


    /**
     * @Route("/espaceclient/paiement/{devisID}/{sommePaiement}", name="step4paiement", methods={"GET","POST"})
     */
    public function step4paiement(Request $request, $devisID, $sommePaiement): Response
    {

        $client = $this->getUser();
        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }
        $listeDevis = $this->devisRepo->findBy(['client' => $client]);
        $devis = $this->devisRepo->find($devisID);

        $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($devis->getDateDepart(), $devis->getDateRetour(), $devis->getVehicule());

        $tarifTotal = $this->tarifsHelper->calculTarifTotal($tarifVehicule, $devis->getOptions(), $devis->getGaranties());

        $tarifJournalier = $tarifTotal / $this->dateHelper->calculDuree($devis->getDateDepart(), $devis->getDateRetour());

        $tarifJournalier = round($tarifJournalier, 2);

        $vingtPourcentTarifTotal = (20 * $tarifTotal) / 100;

        $cinquantePourcentTarifTotal = (50 * $tarifTotal) / 100;

        if ($devis->getClient() == $client) {
            return $this->render('client/reservation/validation/step4paiement.html.twig', [
                'devis' => $devis,
                'tarif' => $tarifVehicule,
                'tarifJournalier' => $tarifJournalier,
                'vingtPourcentTarifTotal' => $vingtPourcentTarifTotal,
                'cinquantePourcentTarifTotal' => $cinquantePourcentTarifTotal,
                'devis' => $devis,
                'tarifTotal' => $tarifTotal,
                'sommePaiement' => $sommePaiement
            ]);
        } else {
            return $this->render('client/reservation/validation/error.html.twig');
        }
    }

    //***********************fin processus validation devis*************** */

    /** 
     * @Route("/espaceclient/reservations", name="client_reservations", methods={"GET","POST"})
     */
    public function listeReservations(Request $request): Response
    {
        $client = $this->getUser();
        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }
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
        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }
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

            $options = [];
            $garanties = [];


            $clientID =  $request->query->get('clientID');
            $agenceDepart = $request->query->get('agenceDepart');
            $agenceRetour = $request->query->get('agenceRetour');
            $lieuSejour = $request->query->get('lieuSejour');
            $dateTimeDepart = $request->query->get('dateTimeDepart');
            $dateTimeRetour = $request->query->get('dateTimeRetour');
            $vehiculeIM = $request->query->get('vehiculeIM');
            $conducteur = $request->query->get('conducteur');
            $arrayOptionsID = (array)$request->query->get('arrayOptionsID');
            $arrayGarantiesID = (array)$request->query->get('arrayGarantiesID');


            $dateDepart = $this->dateHelper->newDate($dateTimeDepart);
            $dateRetour = $this->dateHelper->newDate($dateTimeRetour);


            $vehicule = $this->vehiculeRepo->findByIM($vehiculeIM);
            $client = $this->userRepo->find($clientID);
            $duree = $this->dateHelper->calculDuree($dateDepart, $dateRetour);

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
            // ajout reference dans Entity RESERVATION (CPTGP + year + month + ID)
            $lastID = $this->reservRepo->findBy(array(), array('id' => 'DESC'), 1);
            if ($lastID == null) {
                $currentID = 1;
            } else {
                $currentID = $lastID[0]->getId() + 1;
            }
            $pref = "WEB";
            $reservation->setRefRes($pref, $currentID);

            $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);
            $prix = $this->tarifsHelper->calculTarifTotal($tarifVehicule,  $options, $garanties);

            $reservation->setPrix($prix);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('client_reservations');
        }
        return $this->redirectToRoute('client_reservations');
    }

    /**
     * @Route("/espaceclient/enregistrerDevisWizard", name="client_enregistrerDevisWizard",  methods={"GET","POST"})
     */
    public function enregistrerDevisWizard(Request $request): Response
    {
        $devis = new Devis();

        if ($request->isXmlHttpRequest()) {

            $options = [];
            $garanties = [];


            $agenceDepart = $request->query->get('agenceDepart');
            $agenceRetour = $request->query->get('agenceRetour');
            $lieuSejour = $request->query->get('lieuSejour');
            $dateTimeDepart = $request->query->get('dateTimeDepart');
            $dateTimeRetour = $request->query->get('dateTimeRetour');
            $vehiculeIM = $request->query->get('vehiculeIM');
            $conducteur = $request->query->get('conducteur');
            $arrayOptionsID = (array)$request->query->get('arrayOptionsID');
            $arrayGarantiesID = (array)$request->query->get('arrayGarantiesID');


            $dateDepart = $this->dateHelper->newDate($dateTimeDepart);
            $dateRetour = $this->dateHelper->newDate($dateTimeRetour);


            $vehicule = $this->vehiculeRepo->findByIM($vehiculeIM);
            $client = $this->getUser();
            $duree = $this->dateHelper->calculDuree($dateDepart, $dateRetour);

            $devis->setVehicule($vehicule);
            $devis->setClient($client);
            $devis->setAgenceDepart($agenceDepart);
            $devis->setAgenceRetour($agenceRetour);
            $devis->setDateDepart($dateDepart);
            $devis->setDateRetour($dateRetour);

            //loop sur id des options
            if ($arrayOptionsID != []) {
                for ($i = 0; $i < count($arrayOptionsID); $i++) {

                    $id = $arrayOptionsID[$i];
                    $option = $this->optionsRepo->find($id);
                    array_push($options, $option);
                    $devis->addOption($option);
                }
            }

            //loop sur id des garanties
            if ($arrayOptionsID != []) {

                for ($i = 0; $i < count($arrayGarantiesID); $i++) {

                    $id = $arrayGarantiesID[$i];
                    $garantie = $this->garantiesRepo->find($id);
                    array_push($garanties, $garantie);
                    $devis->addGaranty($garantie);
                }
            }

            $devis->setConducteur($conducteur);
            $devis->setLieuSejour($lieuSejour);
            $devis->setDuree($duree);
            $devis->setDateCreation($this->dateHelper->dateNow());
            // ajout reference dans Entity RESERVATION (CPTGP + year + month + ID)
            $lastID = $this->devisRepo->findBy(array(), array('id' => 'DESC'), 1);

            if ($lastID == null) {
                $currentID = 1;
            } else {

                $currentID = $lastID[0]->getId() + 1;
            }
            $devis->setNumeroDevis($currentID);


            $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);
            $prix = $this->tarifsHelper->calculTarifTotal($tarifVehicule,  $options, $garanties);

            $devis->setPrix($prix);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($devis);
            $entityManager->flush();

            $lastDevis = $this->devisRepo->findBy(array(), array('id' => 'DESC'), 1);
            return new JsonResponse($lastDevis[0]->getId());
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

        // $reservation->setGarantie($devis->getGarantie());


        // $reservation->setSiege($devis->getSiege());

        $arrayOptionsID = $devis->getOptions();
        $arrayGarantiesID = $devis->getGaranties();

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



        $reservation->setPrix($devis->getPrix());
        $reservation->setNumDevis($devis->getNumero());
        $reservation->setDateReservation($this->dateHelper->dateNow());
        $reservation->setCodeReservation('devisTransformé');
        $reservation->setDuree($this->dateHelper->calculDuree($devis->getDateDepart(), $devis->getDateRetour()));
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
        $data['modele'] = $vehicule->getModele()->getLibelle();
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
        $dateDepart = $request->query->get('dateDepart');
        $dateRetour = $request->query->get('dateRetour');

        $dateDepart = $this->dateHelper->newDate($dateDepart);
        $dateRetour = $this->dateHelper->newDate($dateRetour);

        $vehicule = $vehiculeRepo->find($vehicule_id);
        $tarif = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);

        $data = array();

        if ($tarif != null) {

            $data['tarif'] = $tarif;
        } else {
            $data['tarif'] = 0;
        }

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
     * @Route("/espaceclient/devisPDF/{id}", name="devisPDF", methods={"GET","POST"})
     */
    public function devisPDF(Request $request, Devis $devis)
    {

        $data = array();

        $data['numeroDevis'] = $devis->getNumero();
        $data['dateDepart'] = $devis->getDateDepart()->format('d/m/Y H:i');
        $data['dateRetour'] = $devis->getDateRetour()->format('d/m/Y H:i');
        $data['nomClient'] = $devis->getClient()->getNom();
        $data['prenomClient'] = $devis->getClient()->getPrenom();
        $data['vehicule'] = $devis->getVehicule()->getMarque()->getLibelle() . " " . $devis->getVehicule()->getModele()->getLibelle() . " " . $devis->getVehicule()->getImmatriculation();
        $data['duree'] = $devis->getDuree();
        $data['agenceDepart'] = $devis->getAgenceDepart();
        $data['agenceRetour'] = $devis->getAgenceRetour();
        $data['tarif'] = $devis->getPrix();
        $data['adresseClient'] = $devis->getClient()->getAdresse();

        return new JsonResponse($data);
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
            $user->setRecupass($user->getPassword());
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
        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }
        $montant = floatval($request->request->get("montant"));
        //id de la reservation
        $id = $request->request->get("id");
        //$reservation = $this->getDoctrine()->getRepository(Reservation::class)->findOneBy(["client" => $client], ["id" => "DESC"]);
        $reservation = $this->getDoctrine()->getRepository(Reservation::class)->findOneBy(["id" => $id]);
        $modePaiement = $this->getDoctrine()->getRepository(ModePaiement::class)->findOneBy(["id" => 1]);
        $vehicule = new Vehicule();
        if ($reservation == null) {
            return $this->redirectToRoute('espaceClient_index');
        }
        $vehicule = $reservation->getVehicule();
        //$caution = $vehicule->getCaution() * 100;
        $net_a_payer = (($reservaton->getPrix()*$montant)/100);
        // Set your secret key. Remember to switch to your live secret key in production.
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey('sk_test_51INCSpLWsPgEVX5UZKrH0YIs7H7PF8Boao1VcYHEks40it5a39h5KJzcwWxSWUIV6ODWkPS7txKsRyKeSfBknDFC00PAHEBwVP');

        // Token is created using Stripe Checkout or Elements!  
        // Get the payment token ID submitted by the form:
        //$token = $_POST['stripeToken'];
        $token = $request->request->get('stripeToken');
        $charge = \Stripe\Charge::create([
            'amount' => $net_a_payer,
            'currency' => 'eur',
            'source' => $token,
            'description' => 'payement avance pour le véhicule ' . $vehicule->getMarque() . ' ' . $vehicule->getModele() . ' à hauteur de ' . $montant . '% du tarif',
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
        return $this->redirectToRoute('espaceClient_index');
        //return $this->redirectToRoute('client');
    }
}
