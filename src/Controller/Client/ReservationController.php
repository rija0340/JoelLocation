<?php

namespace App\Controller\Client;

use App\Entity\Devis;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Form\DevisClientType;
use App\Service\TarifsHelper;
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
        $res_attente_validation = $this->devisRepo->findBy(['client' => $client, 'transformed' => false], ['dateCreation' => 'DESC']);

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

            $modePaiement = $request->request->get('modePaiement');

            return $this->redirectToRoute('step4paiement', ['devisID' => $devisID, 'modePaiement' => $modePaiement]);
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
                'formClient' => $formClient->createView(),

            ]);
        } else {
            return $this->render('client/reservation/validation/error.html.twig');
        }
    }


    /**
     * @Route("/espaceclient/paiement/{devisID}/{modePaiement}", name="step4paiement", methods={"GET","POST"})
     */
    public function step4paiement(Request $request, $devisID, $modePaiement): Response
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

        if ($modePaiement == 20) {
            $sommePaiement = $this->tarifsHelper->Vingtpourcent($tarifTotal);
        }
        if ($modePaiement == 50) {
            $sommePaiement = $this->tarifsHelper->CinquantePourcent($tarifTotal);
        }
        if ($modePaiement == 100) {
            $sommePaiement = $tarifTotal;
        }

        if ($devis->getClient() == $client) {
            return $this->render('client/reservation/validation/step4paiement.html.twig', [
                'devis' => $devis,
                'tarif' => $tarifVehicule,
                'tarifJournalier' => $tarifJournalier,
                'vingtPourcentTarifTotal' => $vingtPourcentTarifTotal,
                'cinquantePourcentTarifTotal' => $cinquantePourcentTarifTotal,
                'devis' => $devis,
                'tarifTotal' => $tarifTotal,
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


    public function reserverDevis(Devis $devis)
    {

        $devis->setTransformed(true);
        $devisManager =  $this->devisController->getDoctrine()->getManager();
        $devisManager->persist($devis);
        $devisManager->flush();

        $reservation = new Reservation();
        $reservation->setVehicule($devis->getVehicule());
        $reservation->setClient($devis->getClient());
        $reservation->setDateDebut($devis->getDateDepart());
        $reservation->setDateFin($devis->getDateRetour());
        $reservation->setAgenceDepart($devis->getAgenceDepart());
        $reservation->setAgenceRetour($devis->getAgenceRetour());
        $reservation->setNumDevis($devis->getId()); //reference numero devis reservé
        //boucle pour ajout options 
        foreach ($devis->getOptions() as $option) {
            $reservation->addOption($option);
        }

        //boucle pour ajout garantie 

        foreach ($devis->getGaranties() as $garantie) {
            $reservation->addGaranty($garantie);
        }

        $reservation->setPrix($devis->getPrix());
        $reservation->setDateReservation(new \DateTime('NOW'));
        $reservation->setCodeReservation('devisTransformé');
        // ajout reference dans Entity RESERVATION (CPTGP + year + month + ID)
        $lastID = $this->reservRepo->findBy(array(), array('id' => 'DESC'), 1);
        $currentID = $lastID[0]->getId() + 1;
        $reservation->setRefRes("CPTGP", $currentID);

        $entityManager = $this->reservController->getDoctrine()->getManager();
        $entityManager->persist($reservation);
        $entityManager->flush();
        // dump($reservation);
        // die();
    }
}
