<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Classe\Mailjet;
use App\Entity\Paiement;
use App\Entity\Vehicule;
use App\Entity\Conducteur;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Form\AnnulationType;
use App\Form\ConducteurType;
use App\Form\ReportResaType;
use App\Form\KilometrageType;
use App\Form\ReservationType;
use App\Service\TarifsHelper;
use App\Form\AjoutPaiementType;
use App\Form\OptionsGarantiesType;
use App\Repository\UserRepository;
use App\Service\ReservationHelper;
use App\Repository\MarqueRepository;
use App\Repository\ModeleRepository;
use App\Repository\TarifsRepository;
use App\Entity\AnnulationReservation;
use App\Entity\Devis;
use App\Entity\DevisOption;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;
use App\Form\EditClientReservationType;
use App\Repository\ConducteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use App\Form\CollectionFraisSupplResaType;
use App\Repository\AnnulationReservationRepository;
use App\Repository\ModePaiementRepository;
use App\Repository\AppelPaiementRepository;
use App\Repository\DevisOptionRepository;
use App\Repository\DevisRepository;
use App\Service\Site;
use App\Service\EmailManagerService;
use App\Service\ReservationStateService;
use App\Service\PaymentProcessingService;
use App\Service\VehicleAvailabilityService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ReservationController extends AbstractController
{
    private $conducteurRepo;
    private $router;
    private $reservationRepo;
    private $vehiculeRepo;
    private $optionsRepo;
    private $garantiesRepo;
    private $dateHelper;
    private $tarifsHelper;
    private $em;
    private $flashy;
    private $modePaiementRepo;
    private $appelPaiementRepository;
    private $reservationHelper;
    private $devisOptionRepo;
    private $emailManagerService;
    private $reservationStateService;
    private $paymentProcessingService;
    private $vehicleAvailabilityService;

    public function __construct(
        ModePaiementRepository $modePaiementRepo,
        ConducteurRepository $conducteurRepo,
        RouterInterface $router,
        FlashyNotifier $flashy,
        Mailjet $mailjet,
        EntityManagerInterface $em,
        TarifsHelper $tarifsHelper,
        DateHelper $dateHelper,
        ReservationRepository $reservationRepo,
        VehiculeRepository $vehiculeRepo,
        OptionsRepository $optionsRepo,
        GarantieRepository $garantiesRepo,
        AppelPaiementRepository $appelPaiementRepository,
        ReservationHelper $reservationHelper,
        DevisOptionRepository $devisOptionRepo,
        EmailManagerService $emailManagerService,
        ReservationStateService $reservationStateService,
        PaymentProcessingService $paymentProcessingService,
        VehicleAvailabilityService $vehicleAvailabilityService

    ) {

        $this->reservationRepo = $reservationRepo;
        $this->vehiculeRepo = $vehiculeRepo;
        $this->optionsRepo = $optionsRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->dateHelper = $dateHelper;
        $this->tarifsHelper = $tarifsHelper;
        $this->em = $em;
        $this->flashy = $flashy;
        $this->router = $router;
        $this->conducteurRepo = $conducteurRepo;
        $this->modePaiementRepo = $modePaiementRepo;
        $this->appelPaiementRepository = $appelPaiementRepository;
        $this->reservationHelper = $reservationHelper;
        $this->devisOptionRepo = $devisOptionRepo;
        $this->emailManagerService = $emailManagerService;
        $this->reservationStateService = $reservationStateService;
        $this->paymentProcessingService = $paymentProcessingService;
        $this->vehicleAvailabilityService = $vehicleAvailabilityService;
    }

    /**
     * @Route("/backoffice/reservation/", name="reservation_index", methods={"GET"},requirements={"id":"\d+"})
     */
    public function index(ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
    {

        // liste des reservations dont les dates de début sont supérieurs à la date now
        $reservations = $reservationRepository->findNouvelleReservations();

        // $pagination = $paginator->paginate(
        //     $reservations, /* query NOT result */
        //     $request->query->getInt('page', 1)/*page number*/,
        //     50/*limit per page*/
        // );
        return $this->render('admin/reservation/crud/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }


    /**
     * @Route("/backoffice/reservation/new", name="reservation_new", methods={"GET","POST"},requirements={"id":"\d+"})
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

    public function migrateOptionsToDevisOptions(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $devisRepo = $this->getDoctrine()->getRepository(Devis::class);
        $reservationRepo = $this->getDoctrine()->getRepository(Reservation::class);
        $devisOptionRepo = $this->getDoctrine()->getRepository(DevisOption::class);

        // Migrate Devis options
        $allDevis = $devisRepo->findAll();
        foreach ($allDevis as $devis) {
            foreach ($devis->getOptions() as $option) {
                $devisOption = new DevisOption();
                $devisOption->setDevis($devis);
                $devisOption->setOpt($option);
                $devisOption->setQuantity(1);
                $em->persist($devisOption);
            }
        }

        // Migrate Reservation options 
        $allReservations = $reservationRepo->findAll();
        foreach ($allReservations as $reservation) {
            foreach ($reservation->getOptions() as $option) {
                $devisOption = new DevisOption();
                $devisOption->setReservation($reservation);
                $devisOption->setOpt($option);
                $devisOption->setQuantity(1);
                $em->persist($devisOption);
            }
        }

        $em->flush();
        return new Response("Migration completed successfully");
    }

    /**
     * @Route("/backoffice/reservation/details/{id}", name="reservation_show", methods={"GET", "POST"},requirements={"id":"\d+"})
     */
    public function show(Reservation $reservation, Request $request, ReservationHelper $reservationHelper, AnnulationReservationRepository $annulationResaRepo): Response
    {

        $reservations = $this->reservationRepo->findAll();
        foreach ($reservations as $resa) {
            $resa->setDuree($this->dateHelper->calculDuree($resa->getDateDebut(), $resa->getDateFin()));
        }
        $this->em->flush();

        //test nouvelle methode pour calculer diff dates

        $vehicule = $reservation->getVehicule();
        // form pour kilométrage vehicule
        $formKM = $this->createForm(KilometrageType::class, $reservation);
        $formKM->handleRequest($request);

        // form pour ajouter collection de frais supplementaire²
        $formCollectionFraisSupplResa = $this->createForm(CollectionFraisSupplResaType::class, $reservation);
        $formCollectionFraisSupplResa->handleRequest($request);

        //extraction d'un conducteur parmi les conducteurs du client
        $conducteurs = $reservation->getConducteursClient();
        $conducteur = $conducteurs[0];

        //form pour ajout paiement
        $formAjoutPaiement = $this->createForm(AjoutPaiementType::class);
        $formAjoutPaiement->handleRequest($request);

        //form pour report reservation
        $formReportResa = $this->createForm(ReportResaType::class, $reservation);
        $formReportResa->handleRequest($request);



        if ($formAjoutPaiement->isSubmitted() && $formAjoutPaiement->isValid()) {

            $montant = floatval($formAjoutPaiement->getData()['montant']);
            $datePaiement = $formAjoutPaiement->getData()['datePaiement'];
            $modePaiementId = $formAjoutPaiement->getData()['modePaiement'];

            // dd($montant, $datePaiement, $modePaiementId);
            // Use the payment processing service to handle the payment
            $paymentSuccess = $this->paymentProcessingService->addPaymentToReservation(
                $reservation,
                $montant,
                $datePaiement,
                $modePaiementId->getid()
            );

            if (!$paymentSuccess) {
                $this->flashy->error("Erreur sur l'ajout de paiement car le total du paiement est supérieur au dû");
                return $this->redirectToRoute('reservation_show', ['id' => $reservation->getId()]);
            } else {
                // notification pour réussite enregistrement
                $this->flashy->success("L'ajout du paiement a été effectué avec succès");
                return $this->redirectToRoute('reservation_show', ['id' => $reservation->getId()]);
            }
        }

        //gestion de la formulaire kilometrage
        if ($formKM->isSubmitted() && $formKM->isValid()) {
            //sauvegarde données de kilométrage du véhicule
            $reservation->setSaisisseurKm($this->getUser());
            $reservation->setDateKm($this->dateHelper->dateNow());
            // $this->em->persist($vehicule);
            $this->em->flush();

            // notification pour réussite enregistrement
            $this->flashy->success("Les kilométrages sont bien enregistrés");
            return $this->redirectToRoute('reservation_show', ['id' => $reservation->getId()]);
        }


        //gestion form report reservation
        if ($formReportResa->isSubmitted() && $formReportResa->isValid()) {

            $reportSuccess = $this->reservationStateService->reportReservation($reservation);

            if ($reportSuccess) {
                $this->flashy->success("La réservation a été reportée");
                return $this->redirectToRoute('reservation_show', ['id' => $reservation->getId()]);
            } else {
                $this->flashy->error("Erreur lors du report de la réservation");
                return $this->redirectToRoute('reservation_show', ['id' => $reservation->getId()]);
            }
        }

        //form pour annulation reservation
        $annulation = new AnnulationReservation();
        $formAnnulation = $this->createForm(AnnulationType::class, $annulation);
        $formAnnulation->handleRequest($request);

        //gestion annulation reservation
        if ($formAnnulation->isSubmitted() && $formAnnulation->isValid()) {
            $annulationEntity = $formAnnulation->getData();
            $montant = $formAnnulation->get('montant')->getData();
            $cancellationSuccess = $this->reservationStateService->cancelReservation($request, $reservation, $annulationEntity, $montant);

            if (!$cancellationSuccess) {
                $this->flashy->success('Cette réservation est déjà annulée');
                return $this->redirectToRoute('reservation_show', ['id' => $reservation->getId()]);
            } else {
                $this->flashy->success("L'annulation de la réservation N°" . $reservation->getReference() . " a été effectué avec succès");
                return $this->redirectToRoute('reservation_cancel_index');
            }
        }

        //appel à paiement si existe
        $appelPaiement = $this->appelPaiementRepository->findOneBy(['reservation' => $reservation]);

        //gestion ajout frais supplementaire
        //gestion annulation reservation
        if ($formCollectionFraisSupplResa->isSubmitted() && $formCollectionFraisSupplResa->isValid()) {
            foreach ($reservation->getFraisSupplResas() as $fraisSuppl) {
                $fraisSuppl->setReservation($reservation);
                // calculer prix ht frais
                $this->em->persist($fraisSuppl);
            }
            $this->em->flush();
        }

        return $this->render('admin/reservation/crud/show.html.twig', [
            'reservation' => $reservation,
            'hashedId' => sha1($reservation->getId()),
            'formKM' => $formKM->createView(),
            'formAjoutPaiement' => $formAjoutPaiement->createView(),
            'formReportResa' => $formReportResa->createView(),
            'formAnnulation' => $formAnnulation->createView(),
            'appelPaiement' => $appelPaiement,
            'formCollectionFraisSupplResa' => $formCollectionFraisSupplResa->createView(),
            // 'totalFraisHT' => $reservationHelper->getTotalFraisHT($reservation),
            'totalFraisTTC' => $reservationHelper->getTotalFraisTTC($reservation),
            'prixResaTTC' => $reservationHelper->getPrixResaTTC($reservation),
            'totalResaFraisTTC' => $reservationHelper->getTotalResaFraisTTC($reservation),
        ]);
    }

    /**
     * @Route("/backoffice/reservation/{id}/edit", name="reservation_edit", methods={"GET","POST"},requirements={"id":"\d+"})
     */
    public function edit(Request $request, Reservation $reservation): Response
    {

        if ($request->query->has('type') && $request->query->get('type') == 'from_planning') {

            $reference = $request->query->get("reference");

            $reservation = $this->reservationRepo->findOneBy(['reference' => $reference]);

            $dateDepart = $request->query->get("dateDepart");
            $dateRetour = $request->query->get("dateRetour");
            $immatriculation = $request->query->get("vehicule");

            $dateDepart = DateTime::createFromFormat('Y-m-d\TH:i', $dateDepart);
            $dateRetour = DateTime::createFromFormat('Y-m-d\TH:i', $dateRetour);

            $vehicule = $this->vehiculeRepo->findOneBy(['immatriculation' => $immatriculation]);
            $reservation->setVehicule($vehicule);
            //tarif vehicule si custom est renseigné
            if ($request->query->has('has-custom-tarif') && $request->query->get('has-custom-tarif') == 'true') {
                $tarifVeh = intval($request->query->get("custom-tarif"));
            } else {
                //on garde le tarif du véhicule si pas de custom 
                // $tarifVeh = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);
                $tarifVeh = $reservation->getTarifVehicule();
            }
            //duree
            $duree = $this->dateHelper->calculDuree($dateDepart, $dateRetour);
            $reservation->setDuree($duree);
            $reservation->setDateDebut($dateDepart);
            $reservation->setDateFin($dateRetour);
            $reservation->setTarifVehicule($tarifVeh);
            $reservation->setPrix($this->tarifsHelper->calculTarifTotal($tarifVeh, $reservation->getOptions(), $reservation->getGaranties(), $reservation->getConducteur()));

            $this->flashy->success("Modification réussie de la réservation " . $reservation->getReference());

            $this->em->flush();
            return $this->redirectToRoute('planGen');
        }

        $ancientPrix = $reservation->getPrix();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // le champ véhicule ne peux être vide
            $dataForm = $immatriculation = $request->request->get('reservation');
            if ($request->request->get('select') == "") {
                $immatriculation = $dataForm['immatriculation'];
                $vehicule = $this->vehiculeRepo->findOneBy(['immatriculation' => $immatriculation]);
                // $this->flashy->error("Le véhicule ne peut être pas vide");
                // return $this->redirectToRoute('reservation_edit', ['id' => $reservation->getId()]);
            } else {

                $vehicule = $this->vehiculeRepo->find($request->request->get('select'));
            }
            // $tarifVeh = $this->tarifsHelper->calculTarifVehicule($form->getData()->getDateDebut(), $form->getData()->getDateFin(), $vehicule);
            //permettre la modification de prix
            $tarifVeh = $dataForm['tarifVehicule'];
            $reservation->setVehicule($vehicule);
            $duree = $this->dateHelper->calculDuree($form->getData()->getDateDebut(), $form->getData()->getDateFin());
            $reservation->setDuree($duree);

            // si le prix a été modifié
            if ($form->getData()->getPrix() != $ancientPrix) {
                $reservation->setTarifVehicule($tarifVeh);
                $reservation->setPrix($form->getData()->getPrix());
            } else {
                $reservation->setTarifVehicule($tarifVeh);
                $t = $this->tarifsHelper->calculTarifVehicule($form->getData()->getDateDebut(), $form->getData()->getDateFin(), $vehicule);
                $reservation->setPrix($this->tarifsHelper->calculTarifTotal($tarifVeh, $reservation->getOptions(), $reservation->getGaranties(), $reservation->getConducteur()));
            }

            $this->em->flush();

            $this->flashy->success("Modification effectuée");
            return $this->redirectToRoute('reservation_show', ['id' => $reservation->getId()]);
        }

        return $this->render('admin/reservation/crud/edit.html.twig', [
            'reservation' => $reservation,
            'imVeh' => $reservation->getVehicule()->getImmatriculation(), //utile pour val par défaut select
            'form' => $form->createView(),
            'routeReferer' => 'reservation_show'
        ]);
    }

    /**
     * @Route("/backoffice/reservation/{id}", name="reservation_delete", methods={"DELETE"},requirements={"id":"\d+"})
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
     * @Route("/backoffice/reservation/archiver/{id}", name="reservation_archive", methods={"GET", "POST"},requirements={"id":"\d+"})
     */
    public function archiver(Request $request, Reservation $reservation): Response
    {
        $this->reservationStateService->archiveReservation($reservation);

        $this->flashy->success("La réservation N° " . $reservation->getReference() . " a été archivée");
        return $this->redirectToRoute('reservation_index');
    }

    /**
     * @Route("/espaceclient/validation/options-garanties/{id}", name="validation_step2", methods={"GET","POST"})
     * @Route("/backoffice/reservation/modifier/options-garanties/{id}", name="reservation_optionsGaranties_edit", methods={"GET","POST"},requirements={"id":"\d+"})
     *  @Route("/backoffice/devis/modifier-options-garanties/{id}", name="devis_optionsGaranties_edit", methods={"GET","POST"},requirements={"id":"\d+"})
     */
    public function editOptionsGaranties(Request $request, $id, DevisRepository $devisRepo): Response
    {
        // Determine which entity to load based on the route
        $routeName = $request->get('_route');
        $vehiculesAvailable = null;
        $vehiculeIsNotAvailable = null;
        if ($routeName === 'validation_step2') {
            $entity = $devisRepo->find($id);
            //check si vehicule devis est déjà utilisé dans une reservation
            $vehiculeIsNotAvailable = $this->vehicleAvailabilityService->isVehicleInvolvedInReservations(
                $entity->getVehicule(),
                $entity->getDateDepart(),
                $entity->getDateRetour()
            );

            if ($vehiculeIsNotAvailable) {
                $vehiculesAvailable = $this->vehicleAvailabilityService->getAvailableVehicles(
                    $entity->getDateDepart(),
                    $entity->getDateRetour()
                );
            } else {
                $vehiculesAvailable = null;
            }
            // $devis = $this->devisRepo->find($devisID);
            if ($entity->getClient() != $this->getUser()) {
                $this->flashy->error("Le devis n'existe pas");
                return $this->redirectToRoute('espaceClient_index');
            }
        } else if ($routeName === 'reservation_optionsGaranties_edit') {
            $entity = $this->reservationRepo->find($id);
        } else {
            $entity = $devisRepo->find($id);
        }

        // Rest of your logic remains the same since both entities implement OptionsGarantiesInterface
        $form = $this->createForm(OptionsGarantiesType::class, $entity);

        $garanties = $this->garantiesRepo->findAll();
        $options = $this->optionsRepo->findAll();
        $form->handleRequest($request);

        //serializer options et garanties de devis
        $dataOptions = $this->reservationHelper->getOptionsGarantiesAllAndData($entity)["dataOptions"];
        $dataGaranties = $this->reservationHelper->getOptionsGarantiesAllAndData($entity)["dataGaranties"];
        $allOptions = $this->reservationHelper->getOptionsGarantiesAllAndData($entity)["allOptions"];
        $allGaranties = $this->reservationHelper->getOptionsGarantiesAllAndData($entity)["allGaranties"];

        $redirectRoute = "";
        $type = "";
        if ($routeName == "reservation_optionsGaranties_edit") {
            $redirectRoute = "reservation_show";
            $type = "reservation";
        } else if ($routeName == "devis_optionsGaranties_edit") {
            $redirectRoute = "devis_show";
            $type = "devis";
        } else if ($routeName == "validation_step2") {
            $redirectRoute = "validation_step3";
            $type = "devis";
        }

        if ($request->get('editedOptionsGaranties') == "true") {

            $checkboxGaranties = $request->get("checkboxGaranties");
            $conduteur = $request->get('radio-conducteur');

            //changement valeur conducteur
            $conducteur = ($conduteur == "true") ? true : false;
            $entity->setConducteur($conducteur);
            $this->em->flush();

            foreach ($entity->getDevisOptions() as $option) {
                $option->setDevis(null);
                $this->devisOptionRepo->remove($option, true);
            }
            if ($this->reservationHelper->getOptionsFromRequest($request) != []) {

                //save devis options
                $this->reservationHelper->saveDevisOptions($entity, $this->reservationHelper->getOptionsFromRequest($request), $this->em);
            }

            // if ($checkboxOptions != []) {
            //     // tous enlever et puis entrer tous les options
            //     foreach ($entity->getOptions() as $option) {
            //         $entity->removeOption($option);
            //     }
            //     for ($i = 0; $i < count($checkboxOptions); $i++) {
            //         $entity->addOption($this->optionsRepo->find($checkboxOptions[$i]));
            //     }
            //     $this->em->flush();
            // } else {
            //     // si il y a des options, les enlever
            //     if (count($entity->getOptions()) > 0) {
            //         foreach ($entity->getOptions() as $option) {
            //             $entity->removeOption($option);
            //         }
            //     }
            //     $this->em->flush();
            // }

            if ($checkboxGaranties != []) {
                // tous enlever et puis entrer tous les garanties
                foreach ($entity->getGaranties() as $garantie) {
                    $entity->removeGaranty($garantie);
                }
                for ($i = 0; $i < count($checkboxGaranties); $i++) {
                    $entity->addGaranty($this->garantiesRepo->find($checkboxGaranties[$i]));
                }
                $this->em->flush();
            } else {
                // si il y a des garanties, les enlever
                if (count($entity->getGaranties()) > 0) {
                    foreach ($entity->getGaranties() as $garantie) {
                        $entity->removeGaranty($garantie);
                    }
                }
                $this->em->flush();
            }

            $entity->setPrixGaranties($this->tarifsHelper->sommeTarifsGaranties($entity->getGaranties()));
            $entity->setPrixOptions($this->tarifsHelper->sommeTarifsOptions($this->reservationHelper->getOptionsFromRequest($request), $entity->getConducteur()));
            $entity->setPrix($entity->getTarifVehicule() + $entity->getPrixOptions() + $entity->getPrixGaranties());

            $this->em->flush();

            return $this->redirectToRoute($redirectRoute, ['id' => $entity->getId()]);
        }


        $template = $routeName === 'validation_step2' ? 'client2/reservation/nouvelle_resa/step3.html.twig' : 'admin/devis_reservation/options_garanties/edit.html.twig';
        return $this->render($template, [
            'form' => $form->createView(),
            'devis' => $entity,
            'routeReferer' => $redirectRoute,
            'dataOptions' => $dataOptions,
            'dataGaranties' => $dataGaranties,
            'allOptions' => $allOptions,
            'allGaranties' => $allGaranties,
            'conducteur' => $entity->getConducteur(),
            'cancelPath' => $this->getCancelPath($routeName, $entity->getId()),
            'type' => $type, //doit etre dnamique si devis aussi utilise cette fonction 
            //var pour validation
            'vehiculesAvailable' => $vehiculesAvailable,
            'vehiculeIsNotAvailable' => $vehiculeIsNotAvailable,
            'tarifVehicule' => $entity->getTarifVehicule(),
            'duree' => $entity->getDuree(),
            'prixConductSuppl' => $this->tarifsHelper->getPrixConducteurSupplementaire(),
            'step' => 'validation'
        ]);
    }
    private function getCancelPath(string $routeName, $entityId): string
    {
        if ($routeName == "reservation_optionsGaranties_edit") {
            $route = "reservation_show";
            return $this->generateUrl($route, ['id' => $entityId]);
        } else if ($routeName == "devis_optionsGaranties_edit") {
            $route = "devis_show";
            return $this->generateUrl($route, ['id' => $entityId]);
        } else if ($routeName == "validation_step2") {
            $route = "client_reservations";
            return $this->generateUrl($route);
        }
    }

    /**
     * @Route("/backoffice/reservation/envoi-identification-connexion/{id}", name="reservation_ident_connex", methods={"GET","POST"},requirements={"id":"\d+"})
     */
    public function envoyerIdentConnex(Request $request, Reservation $reservation, UserPasswordEncoderInterface $passwordEncoder): Response
    {


        $mail = $reservation->getClient()->getMail();
        $nom = $reservation->getClient()->getNom();
        $mdp = uniqid();
        $content = "Bonjour, " . '<br>' . "voici vos identifications de connexion." . '<br>' . " Mot de passe: " . $mdp . '<br>' . "Email : votre email";

        $reservation->getClient()->setPassword($passwordEncoder->encodePassword(
            $reservation->getClient(),
            $mdp
        ));

        $this->em->flush();
        $this->mail->send($mail, $nom, "Identifiants de connexion", $content);


        $this->flashy->success("Les identifians de connexion du client ont été envoyés");
        return $this->redirectToRoute($this->getReferer($request), ['id' => $reservation->getId()]);
    }




    /**
     *  @Route("/backoffice/reservation/modifier/{id}/infos-client/", name="reservation_infosClient_edit", methods={"GET","POST"},requirements={"id":"\d+"})
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
            return $this->redirectToRoute('reservation_show', ['id' => $reservation->getId()]);
        }

        return $this->render('admin/reservation/crud/infos_client/edit.html.twig', [

            'form' => $form->createView(),
            'reservation' => $reservation,
            'routeReferer' => 'reservation_show'

        ]);
    }

    /**
     *  @Route("/backoffice/reservation/ajouter-conducteur/{reservation}", name="add_conducteur", methods={"GET","POST"},requirements={"id":"\d+"})
     *
     */
    public function addConducteur(Request $request, Reservation $reservation): Response
    {

        $conducteur = new Conducteur();
        $form = $this->createForm(ConducteurType::class, $conducteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isSubmitted()) {

            // ajouter un conducteur
            $conducteur->setIsPrincipal(false);
            $conducteur->setClient($reservation->getClient());
            $this->em->persist($conducteur);
            $this->em->flush();

            // ajouter conducteur à une réservation
            $reservation->addConducteursClient($conducteur);
            $this->em->flush();

            //notification succes
            $this->flashy->success('Le conducteur a bienc été ajouté');
            return $this->redirectToRoute('reservation_show', ['id' => $reservation->getId()]);
        }

        $conducteurs = $this->conducteurRepo->findBy(['client' => $reservation->getClient()]);
        $nbConducteurs = count($conducteurs);

        //check if conducteur n'est pas lié à cetter resa
        foreach ($conducteurs as $key => $conducteur) {
            if ($conducteur->getReservations()->contains($reservation)) {
                unset($conducteurs[$key]);
            }
        }

        // i need the total number of conducteur and i need also those how are not yet added to a resa 


        return $this->render('admin/reservation/crud/conducteur/new.html.twig', [
            'conducteurs' => $conducteurs,
            'nbConducteurs' => $nbConducteurs,
            'form' => $form->createView(),
            'reservation' => $reservation,
            'routeReferer' => 'reservation_show'
        ]);
    }

    /**
     * from autocompletion input
     *  @Route("/backoffice/reservation/ajouter-conducteur-selection/", name="add_selected_conducteur", methods={"GET","POST"},requirements={"id":"\d+"})
     *
     */
    public function addSelectedConducteur(Request $request)
    {
        //extracion mail from string format : "nom prenom (mail)"
        $conducteurs = $request->request->get('selectedConducteurs');

        $conducteur = $this->conducteurRepo->find(intval($conducteurs[0]));

        $reservation = $this->reservationRepo->find($request->request->get('idReservation'));

        foreach ($conducteurs as $key => $conducteur) {
            $conducteur = $this->conducteurRepo->find(intval($conducteur));
            $reservation->addConducteursClient($conducteur);
        }

        $this->em->flush();

        $this->flashy->success("Le conducteur a été ajouté aved succès");
        return $this->redirectToRoute('reservation_show', ['id' => $reservation->getId()]);
    }


    /**
     *  @Route("/backoffice/reservation/liste-conducteurs/", name="liste_conducteurs", methods={"GET","POST"},requirements={"id":"\d+"})
     *
     */
    public function listeConduteurs(Request $request): Response
    {
        $id = $request->query->get('idReservation');
        $reservation = $this->reservationRepo->find($id);
        $conducteurs = $this->conducteurRepo->findBy(['client' => $reservation->getClient()]);

        $data = array();
        foreach ($conducteurs as $key => $conducteur) {

            if (count($conducteur->getReservations()) == 0) {
                $data[$key]['nom'] = $conducteur->getNom();
                $data[$key]['prenom'] = $conducteur->getPrenom();
                $data[$key]['numPermis'] = $conducteur->getNumeroPermis();
            }
        }


        return new JsonResponse($data);
    }


    /**
     * @Route("/backoffice/reservation/modifier-conducteur/{id}/{reservation}", name="reservation_conducteur_edit", methods={"GET","POST"})
     */
    public function editConducteur(Request $request, Conducteur $conducteur, Reservation $reservation): Response
    {
        $form = $this->createForm(ConducteurType::class, $conducteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            $this->flashy->success('Votre conducteur a bien été modifié');
            return $this->redirectToRoute('reservation_show', ['id' => $reservation->getId()]);
        }

        return $this->render('admin/reservation/crud/conducteur/edit.html.twig', [
            'form' => $form->createView(),
            'routeReferer' => 'reservation_show',
            'reservation' => $reservation

        ]);
    }


    /**
     * @Route("/backoffice/reservation/supprimer-conducteur/{id}/{reservation}", name="reservation_conducteur_delete", methods={"DELETE"},requirements={"id":"\d+"})
     */
    public function deleteConducteur(Request $request, Conducteur $conducteur, Reservation $reservation): Response
    {
        $id = $this->reservationRepo->find($request->request->get('reservation'));
        $reservation = $this->reservationRepo->find($id);
        if ($this->isCsrfTokenValid('delete' . $conducteur->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($conducteur);
            $entityManager->flush();
            $this->flashy->success('le conducteur a été supprimé');
            return $this->redirectToRoute('reservation_show', ['id' => $reservation->getId()]);
        }
    }


    /**
     * @Route("/backoffice/reservation/effacer-paiement/{id}/{reservation}", name="reservation_paiement_delete", methods={"DELETE"})
     */
    public function deletePaiement(Request $request, Paiement $paiement, Reservation $reservation): Response
    {
        if ($this->isCsrfTokenValid('delete' . $paiement->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($paiement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('reservation_show', ['id' => $reservation->getId()]);
    }

    /**
     * @Route("/backoffice/reservation/retour-anticipe/{id}", name="reservation_retour_anticipe", methods={"GET", "POST"})
     */
    public function retourAnticipe(Request $request, Reservation $reservation): Response
    {
        $this->reservationStateService->processEarlyReturn($reservation);

        $this->flashy->success("Le retour anticipé a été éfféctué avec succès");

        return $this->redirectToRoute('reservation_show', ['id' => $reservation->getId()]);
    }

    /**
     * @Route("/backoffice/reservation/resas-non-solde", name="reserv_non_solde", methods={"GET"})
     */
    public function resas_non_solde(): Response
    {
        $reservations = $this->reservationRepo->findResasNonSoldes();
        return $this->render('admin/reservation/non_solde/reserv_non_solde.html.twig', [
            'reservations' => $reservations
        ]);
    }

    /**
     * @Route("/backoffice/reservation/envoyer-contrat-pdf/{id}", name="envoyer_contrat", methods={"GET","POST"},requirements={"id":"\d+"})
     */
    public function envoyerContrat(Request $request, Reservation $reservation): Response
    {

        $this->emailManagerService->sendContrat($request, $reservation);

        return $this->redirectToRoute('reservation_show', ['id' => $reservation->getId()]);
    }

    /**
     * @Route("/backoffice/reservation/envoyer-facture-pdf/{id}", name="envoyer_facture", methods={"GET","POST"},requirements={"id":"\d+"})
     */
    public function envoyerFacture(Request $request, Reservation $reservation): Response
    {
        $this->emailManagerService->sendFacture($request, $reservation);

        return $this->redirectToRoute('reservation_show', ['id' => $reservation->getId()]);
    }


    /**
     * @Route("/backoffice/reservation/reservations-par-vehicule/{id}", name="reservations_par_vehicule", methods={"GET","POST"},requirements={"id":"\d+"})
     */
    public function reservationsByVehicule(Vehicule $vehicule): Response
    {

        $reservations = $this->reservationRepo->findBy(['vehicule' => $vehicule]);

        return $this->render('admin/reservation/liste_par_vehicule/index.html.twig', [
            'reservations' => $reservations,
            'vehicule' => $vehicule

        ]);
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
