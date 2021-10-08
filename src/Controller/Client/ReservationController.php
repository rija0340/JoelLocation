<?php

namespace App\Controller\Client;

use Stripe\Stripe;
use App\Entity\Devis;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Form\ClientInfoType;
use Stripe\Checkout\Session;
use App\Form\DevisClientType;
use App\Service\TarifsHelper;
use App\Classe\ReservationClient;
use App\Repository\DevisRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{

    private $reservationRepo;
    private $devisRepo;
    private $garantiesRepo;
    private $optionsRepo;
    private $dateHelper;
    private $tarifsHelper;
    private $vehiculeRepo;

    public function __construct(
        ReservationRepository $reservationRepo,
        DevisRepository $devisRepo,
        OptionsRepository $optionsRepo,
        GarantieRepository $garantiesRepo,
        DateHelper $dateHelper,
        TarifsHelper $tarifsHelper,
        VehiculeRepository $vehiculeRepo

    ) {
        $this->reservationRepo = $reservationRepo;
        $this->devisRepo = $devisRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->optionsRepo = $optionsRepo;
        $this->dateHelper = $dateHelper;
        $this->tarifsHelper = $tarifsHelper;
        $this->vehiculeRepo = $vehiculeRepo;
    }

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
        $reservationEffectuers = $this->reservationRepo->findReservationEffectuers($client, $date);

        //récupération des réservations en cours
        $reservationEncours = $this->reservationRepo->findReservationEncours($client, $date);

        $res_attente_dateDebut = $this->reservationRepo->findReservationsAttenteDateDebut($client, $date);

        //récupération des réservation en attente (devis envoyé et en attente de validation par client)
        // $reservationEnAttentes = $this->reservRepo->findReservationEnAttente($client, $date);
        $devis = $this->devisRepo->findBy(['client' => $client, 'transformed' => false], ['dateCreation' => 'DESC']);

        return $this->render('client/reservation/index.html.twig', [
            'reservation_effectuers' => $reservationEffectuers,
            'reservation_en_cours' => $reservationEncours,
            'devis' => $devis,
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
            'form' => $form->createView(),
            'devisID' => $devisID
        ]);
    }


    /**
     * @Route("/espaceclient/infosClient/{devisID}", name="step3infosClient", methods={"GET","POST"})
     */
    public function step3infosClient(Request $request, $devisID, ReservationClient $reservationClientSession): Response
    {
        $garanties = $request->query->get('garanties');
        $devis = $this->devisRepo->find($devisID);
        // dd($devis->getGaranties());
        // for ($i = 0; $i < count($garanties); $i++) {
        // }


        $client = $this->getUser();
        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }
        $listeDevis = $this->devisRepo->findBy(['client' => $client]);
        $devis = $this->devisRepo->find($devisID);

        $formClient = $this->createForm(ClientInfoType::class, $client);
        $formClient->handleRequest($request);


        if ($formClient->isSubmitted() && $formClient->isValid()) {


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($client);
            $entityManager->flush();
            $reservationClientSession->addModePaiment($request->request->get('modePaiement'));

            // return $this->redirectToRoute('step4paiement', ['devisID' => $devisID]);
            //tester stripe
            return $this->redirectToRoute('paiementStripe', ['devisID' => $devisID]);
        }

        if ($devis->getClient() == $client) {
            return $this->render('client/reservation/validation/step3infosClient.html.twig', [
                'devis' => $devis,
                'formClient' => $formClient->createView(),

            ]);
        } else {
            return $this->render('client/reservation/validation/error.html.twig');
        }
    }

    //test de paiement par stripe
    /**
     * @Route("/espaceclient/paiement-stripe/{devisID}", name="paiementStripe", methods={"GET","POST"})
     */
    public function paiementStripe(Request $request, $devisID)
    {

        $devis = $this->devisRepo->find($devisID);


        Stripe::setApiKey('sk_test_51JiGijGsAu4Sp9QQtyfjOoOQMb6kfGjE1z50X5vrW6nS7wLtK5y2HmodT3ByrI7tQl9dsvP69fkN4vVfH5676nDo00VgFOzXct');

        $YOUR_DOMAIN = 'http://127.0.0.1:8000';
        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Réservation du ' . $devis->getDateDepart()->format('d/m/Y H:i') . " au " . $devis->getDateRetour()->format('d/m/Y H:i'),
                        'images' => [$YOUR_DOMAIN . "/uploads/vehicules" . $devis->getVehicule()->getImage()],
                        'description' => $devis->getVehicule()->getMarque() . " " . $devis->getVehicule()->getModele()
                    ],
                    'unit_amount' => $devis->getPrix() * 100
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
        ]);
        return $this->redirect($checkout_session->url);
    }

    /**
     * @Route("/espaceclient/paiement/{devisID}", name="step4paiement", methods={"GET","POST"})
     */
    public function step4paiement(Request $request, $devisID,  ReservationClient $reservationClientSession): Response
    {

        $client = $this->getUser();
        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }
        $listeDevis = $this->devisRepo->findBy(['client' => $client]);
        $devis = $this->devisRepo->find($devisID);

        $modePaiement = $reservationClientSession->getModePaiment();
        dd($modePaiement);
        if ($modePaiement == 25) {
            $sommePaiement = $this->tarifsHelper->VingtCinqPourcent($devis->getPrix());
        }
        if ($modePaiement == 50) {
            $sommePaiement = $this->tarifsHelper->CinquantePourcent($devis->getPrix());
        }
        if ($modePaiement == 100) {
            $sommePaiement = $devis->getPrix();
        }

        if ($devis->getClient() == $client) {
            return $this->render('client/reservation/validation/step4paiement.html.twig', [
                'devis' => $devis,
                'sommePaiement' => $sommePaiement,
            ]);
        } else {
            return $this->render('client/reservation/validation/error.html.twig');
        }
    }

    //***********************fin processus validation devis*************** */

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

            $devis->setTransformed(0);

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
            // $lastDevis = $this->devisRepo->findBy(array(), array('id' => 'DESC'), 1);
            // return new JsonResponse($lastDevis[0]->getId());
        }
        return $this->redirectToRoute('client_reservations');
    }
}
