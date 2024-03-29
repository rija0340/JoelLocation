<?php

namespace App\Controller;

use App\Classe\Mailjet;
use App\Entity\User;
use App\Entity\Devis;
use App\Entity\Tarifs;
use GuzzleHttp\Client;
use App\Entity\Paiement;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Service\TarifsHelper;
use App\Classe\ReservationSession;
use App\Form\ReservationStep1Type;
use App\Repository\UserRepository;
use App\Service\ReservationHelper;
use App\Form\ClientNewComptoirType;
use App\Form\Step4SelectClientType;
use App\Repository\DevisRepository;
use App\Repository\MarqueRepository;
use App\Repository\ModeleRepository;
use App\Repository\TarifsRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\ModePaiementRepository;
use App\Repository\ModeReservationRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/backoffice")
 */
class VenteComptoirController extends AbstractController
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
    private $flashy;
    private $passwordEncoder;
    private $devisRepo;
    private $reservationSession;
    private $mail;
    private $reservationHelper;
    private $modePaiementRepo;
    private $modeReservationRepo;
    private $tarifConductSuppl;

    public function __construct(
        ModeReservationRepository $modeReservationRepo,
        ModePaiementRepository $modePaiementRepo,
        FlashyNotifier $flashy,
        EntityManagerInterface $em,
        MarqueRepository $marqueRepo,
        ModeleRepository $modeleRepo,
        TarifsHelper $tarifsHelper,
        DateHelper $dateHelper,
        TarifsRepository $tarifsRepo,
        ReservationRepository $reservationRepo,
        UserRepository $userRepo,
        VehiculeRepository $vehiculeRepo,
        OptionsRepository $optionsRepo,
        GarantieRepository $garantiesRepo,
        UserPasswordEncoderInterface $passwordEncoder,
        DevisRepository $devisRepo,
        ReservationSession $reservationSession,
        Mailjet $mail,
        ReservationHelper $reservationHelper
    ) {

        $this->reservationSession = $reservationSession;
        $this->flashy = $flashy;
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
        $this->passwordEncoder = $passwordEncoder;
        $this->devisRepo = $devisRepo;
        $this->mail = $mail;
        $this->reservationHelper = $reservationHelper;
        $this->modePaiementRepo = $modePaiementRepo;
        $this->modeReservationRepo = $modeReservationRepo;
        $this->tarifConductSuppl = $this->tarifsHelper->getPrixConducteurSupplementaire();
    }

    /**
     * @Route("/vente-comptoir/etape1", name="step1", methods={"GET","POST"})
     */
    public function step1(Request $request, SessionInterface $session): Response
    {
        //remove contenu session avant toute chose

        $this->reservationSession->removeReservation();

        $form = $this->createForm(ReservationStep1Type::class);
        if ($this->reservationSession->getReservation() != null) {
            $form->get('lieuSejour')->setData('Mety sa tsia');
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //recupération donnés envoyé par le formulaire
            $dateDepart = $form->getData()['dateDepart'];
            $dateRetour = $form->getData()['dateRetour'];
            $agenceDepart = $form->getData()['agenceDepart'];
            $agenceRetour = $form->getData()['agenceRetour'];
            $typeVehicule = $form->getData()['typeVehicule'];
            $lieuSejour = $form->getData()['lieuSejour'];

            //stockage information dans session
            $this->reservationSession->addAgenceDepart($agenceDepart);
            $this->reservationSession->addAgenceRetour($agenceRetour);
            $this->reservationSession->addDateDepart($dateDepart);
            $this->reservationSession->addDateRetour($dateRetour);
            $this->reservationSession->addTypeVehicule($typeVehicule);
            $this->reservationSession->addLieuSejour($lieuSejour);

            //           dd( $this->reservationSession->getReservation());

            return $this->redirectToRoute('step2');
        }

        return $this->render('admin/vente_comptoir2/step1.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/vente-comptoir/etape2", name="step2", methods={"GET","POST"})
     */
    public function step2(Request $request, PaginatorInterface $paginator): Response
    {

        if ($this->reservationSession->getReservation() == null) {
            return $this->redirectToRoute('step1');
        }

        $dateDepart = $this->reservationSession->getDateDepart();
        $dateRetour = $this->reservationSession->getDateRetour();

        //un tableau contenant les véhicules utilisées dans les reservations se déroulant entre 
        //$dateDepart et $dateRetour
        $reservations = $this->reservationRepo->findReservationIncludeDates($dateDepart, $dateRetour);
        $vehiculesDisponible = $this->reservationHelper->getVehiculesDisponible($reservations);

        //ajout id véhicule dans session, erreur si on stock directement 
        //un objet vehicule dans session et ensuite on enregistre dans base de donnée
        if ($request->request->get('vehicule') != null) {

            $tarifVehicule = $request->request->get('tarifVehicule');
            $id_vehicule = $request->request->get('vehicule');

            if ($tarifVehicule != null) {
                $this->reservationSession->addTarifVehicule($tarifVehicule);
            } else {
                $this->reservationSession->addTarifVehicule(null);
            }

            $this->reservationSession->addVehicule($id_vehicule);

            return $this->redirectToRoute('step3');
        }

        $data = [];
        foreach ($vehiculesDisponible as $key => $veh) {
            $tarif = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $veh);
            $data[$key]['vehicule'] = $veh;
            $data[$key]['tarif'] = $tarif;
        }

        // dd($session->get('step1', []));
        //utilisation de paginator pour liste véhicule disponible
        //pagination
        $vehiculesDisponiblePagination = $paginator->paginate(
            $vehiculesDisponible, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            3 // Nombre de résultats par page
        );

        // dd($vehiculesDisponible);

        return $this->render('admin/vente_comptoir2/step2.html.twig', [
            'vehiculesDisponiblePagination' => $vehiculesDisponiblePagination,
            'vehiculesDisponible' => $vehiculesDisponible,
            'data' => $data,
            'dateDepart' => $dateDepart,
            'dateRetour' => $dateRetour,
            'session' => $this->reservationSession->getReservation()
        ]);
    }

    /**
     * @Route("/vente-comptoir/etape3", name="step3", methods={"GET","POST"})
     */
    public function step3(Request $request)
    {

        if ($this->reservationSession->getReservation() == null) {
            return $this->redirectToRoute('step1');
        }
        //recupérer liste options et  garanties dans base de données
        $options = $this->optionsRepo->findAll();
        $garanties = $this->garantiesRepo->findAll();

        // recuperation donnée from formulaire options et garanties
        if ($request->request->get('radio-conducteur') != null) {

            //$optionsData et garantiesData sont des tableaux 
            //(mettre un "[]" apres les noms des input type checkbox dans templates pour obtenir tous les  checkbox cochés)
            $conducteur = $request->request->get('radio-conducteur');
            //options peut être null
            if ($request->get('checkboxOptions') != null) {
                $optionsData = $request->request->get('checkboxOptions');
            }

            if ($request->get('checkboxGaranties') != null) {
                $garantiesData = $request->request->get('checkboxGaranties');
            }

            //ajout options et garanties (tableau d'objets) dans session 
            if ($request->get('checkboxOptions') != null) {
                $this->reservationSession->addOptions($optionsData);
            }

            if ($request->get('checkboxGaranties') != null) {
                $this->reservationSession->addGaranties($garantiesData);
            }
            $this->reservationSession->addConducteur($conducteur);

            return $this->redirectToRoute('step4');
        }


        $dateDepart = $this->reservationSession->getDateDepart();
        $dateRetour = $this->reservationSession->getDateRetour();
        $vehicule = $this->vehiculeRepo->find($this->reservationSession->getVehicule());

        //si l'admin a entrée un autre tarif dans étape 2, alors on considère ce tarif
        if ($this->reservationSession->getTarifVehicule()) {
            $tarifVehicule = $this->reservationSession->getTarifVehicule();
        } else {
            $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);
        }
        return $this->render('admin/vente_comptoir2/step3.html.twig', [

            'options' => $options,
            'garanties' => $garanties,
            'vehicule' => $vehicule,
            'tarifVehicule' => $tarifVehicule,
            'duree' => $this->dateHelper->calculDuree($dateDepart, $dateRetour),
            'agenceDepart' => $this->reservationSession->getAgenceDepart(),
            'dateDepart' => $this->reservationSession->getDateDepart(),
            'agenceRetour' => $this->reservationSession->getAgenceRetour(),
            'dateRetour' => $this->reservationSession->getDateRetour(),

        ]);
    }


    /**
     * @Route("/vente-comptoir/etape4", name="step4", methods={"GET","POST"})
     */
    public function step4(Request $request): Response
    {


        // securité pour empecher de sauter directement d'une étape à d'autre
        if ($this->reservationSession->getReservation() == null) {
            return $this->redirectToRoute('step1');
        }

        $form = $this->createForm(ClientNewComptoirType::class);
        $form->handleRequest($request);
        //creation de nouveau client 
        if ($form->isSubmitted() && $form->isValid()) {

            // vérification du client si existe déjà dans base de données
            $user = $this->userRepo->findOneBy(['mail' => $form->getData()['email']]);
            if ($user) {
                $this->flashy->error('Ce client existe déjà');
                return $this->redirectToRoute('step4');
            } else {
                //si le client n'existe pas encore alors creer le nouveau client
                $user = new User();
                $user->setNom($form->getData()['nom']);
                $user->setPrenom($form->getData()['prenom']);
                $user->setTelephone($form->getData()['telephone']);
                $user->setMail($form->getData()['email']);
                $user->setRoles(['ROLE_CLIENT']);
                $user->setPassword($this->passwordEncoder->encodePassword(
                    $user,
                    '0000'
                ));
                $user->setUsername($form->getData()['nom']);
                $user->setPresence(true);
                $user->setDateInscription($this->dateHelper->dateNow());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $this->flashy->success('Le client a bien été crée');

                return $this->redirectToRoute('step4');
                // $form->
            }
        }

        $dateDepart = $this->reservationSession->getDateDepart();
        $dateRetour = $this->reservationSession->getDateRetour();
        $vehicule = $this->vehiculeRepo->find($this->reservationSession->getVehicule());

        //si l'admin a entrée un autre tarif dans étape 2, alors on considère ce tarif
        if ($this->reservationSession->getTarifVehicule()) {
            $tarifVehicule = $this->reservationSession->getTarifVehicule();
        } else {
            $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);
        }

        if ($this->reservationSession->getConducteur() == "true") {
            $tarifTotal = $this->tarifConductSuppl +  $this->tarifsHelper->calculTarifTotal($tarifVehicule, $this->optionsObjectsFromSession(), $this->garantiesObjectsFromSession());
        } else {
            $tarifTotal = $this->tarifsHelper->calculTarifTotal($tarifVehicule, $this->optionsObjectsFromSession(), $this->garantiesObjectsFromSession());
        }

        return $this->render('admin/vente_comptoir2/step4.html.twig', [

            'form' => $form->createView(),
            'vehicule' => $vehicule,
            'tarifVehicule' => $tarifVehicule,
            'duree' => $this->dateHelper->calculDuree($dateDepart, $dateRetour),
            'agenceDepart' => $this->reservationSession->getAgenceDepart(),
            'dateDepart' => $this->reservationSession->getDateDepart(),
            'agenceRetour' => $this->reservationSession->getAgenceRetour(),
            'dateRetour' => $this->reservationSession->getDateRetour(),
            'options' => $this->optionsObjectsFromSession(),
            'garanties' => $this->garantiesObjectsFromSession(),
            'conducteur' => $this->reservationSession->getConducteur(),
            'tarifTotal' => $tarifTotal

        ]);
    }

    //enregistrement de devis dans base de données sans envoi mail au client

    /**
     * @Route("/vente-comptoir/enregistrer-devis", name="save_only_devis", methods={"GET","POST"})
     */
    public function saveOnlyDevis(Request $request): Response
    {
        // check if a similar devis already exists
        $result = $this->saveDevis($request);

        if ($result != "devisExist") {
            $numDevis = $result;
            $this->flashy->success('Le devis numero ' . $numDevis . ' a été enregistré avec succés');
            return $this->redirectToRoute('devis_index');
        } else {
            $this->flashy->error("Un devis similaire existe déjà !");
            return $this->redirectToRoute('devis_index');
        }
    }

    //enregistrement de devis dans base de données sans envoi mail au client

    /**
     * @Route("/vente-comptoir/enregistrer-devis-envoi-mail", name="save_devis_send_mail", methods={"GET","POST"})
     */
    public function saveDevisSendMail(Request $request): Response
    {

        $result = $this->saveDevis($request);

        if ($result != "devisExist") {
            $numDevis = $result;
            //url de téléchargement du devis
            $devis = $this->devisRepo->findOneBy(['numero' => $numDevis]);
            $this->reservationHelper->sendMailConfirmationDevis($devis);
            $this->flashy->success('Le devis a été enregistré avec succés et un mail a été envoyé au client');
            return $this->redirectToRoute('devis_index');
        } else {
            $this->flashy->error("Un devis similaire existe déjà !");
            return $this->redirectToRoute('devis_index');
        }
    }

    public function saveDevis($request)
    {

        //extracion mail from string format : "nom prenom (mail)"
        $client = $request->request->get('client');
        $client = explode('(', $client);
        $mailClient = explode(')', $client[1]);
        $mailClient = $mailClient[0];

        //recherche du client correspondant au mail
        $client = $this->userRepo->findOneBy(['mail' => $mailClient]);
        //ajout client dans session
        $this->reservationSession->addClient($client);

        //utile pour eviter erreur new entity, cette erreur apparait lorsque on utilise directement objet véhicule dans session
        $vehicule = $this->vehiculeRepo->find($this->reservationSession->getVehicule());
        // dd($vehicule);

        $isDevisExist = $this->devisRepo->findOneBy([
            'dateDepart' => $this->reservationSession->getDateDepart(),
            'dateRetour' => $this->reservationSession->getDateRetour(),
            'client' => $client,
            'vehicule' => $vehicule
        ]);

        if ($isDevisExist == null) {
            //trouver les options et garanties à l'aide des ID 
            //ajout de ID unique dans la base pour pouvoir telecharger par un lien envoyé au client par mail
            //enregistrement session dans devis
            $devis = new Devis();

            $devis->setDownloadId(uniqid());

            $devis->setAgenceDepart($this->reservationSession->getAgenceDepart());
            $devis->setAgenceRetour($this->reservationSession->getAgenceRetour());
            $devis->setDateDepart($this->reservationSession->getDateDepart());
            $devis->setDateRetour($this->reservationSession->getDateRetour());
            $devis->setVehicule($vehicule);
            $devis->setLieuSejour($this->reservationSession->getLieuSejour());
            $devis->setClient($this->reservationSession->getClient());
            $devis->setDateCreation($this->dateHelper->dateNow());

            $devis->setDuree($this->dateHelper->calculDuree($this->reservationSession->getDateDepart(), $this->reservationSession->getDateRetour()));

            // si l'admin a entrée un autre tarif dans étape 2, alors on considère ce tarif
            if ($this->reservationSession->getTarifVehicule()) {
                $tarifVehicule = $this->reservationSession->getTarifVehicule();
            } else {
                $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($this->reservationSession->getDateDepart(), $this->reservationSession->getDateRetour(), $vehicule);
            }
            $devis->setTarifVehicule($tarifVehicule);
            $prixOptions = $this->tarifsHelper->sommeTarifsOptions($this->optionsObjectsFromSession());
            $devis->setPrixOptions($prixOptions);
            $prixGaranties = $this->tarifsHelper->sommeTarifsGaranties($this->garantiesObjectsFromSession());
            $devis->setPrixGaranties($prixGaranties);

            //si le client a choisi conducteur optionnel on ajoute 50€
            if ($this->reservationSession->getConducteur() == "true") {
                $devis->setConducteur(true);
                $devis->setPrix($tarifVehicule + $prixGaranties + $prixOptions + $this->tarifConductSuppl);
            } else if ($this->reservationSession->getConducteur() == "false") {
                $devis->setConducteur(false);
                $devis->setPrix($tarifVehicule + $prixGaranties + $prixOptions);
            }

            $devis->setTransformed(false);

            //options et garanties sont des tableaux d'objet dans session
            // checker si options n'est pas null
            if ($this->optionsObjectsFromSession() != null) {
                foreach ($this->optionsObjectsFromSession() as $option) {
                    $devis->addOption($option);
                }
            }
            if ($this->garantiesObjectsFromSession() != null) {
                foreach ($this->garantiesObjectsFromSession() as $garantie) {
                    $devis->addGaranty($garantie);
                }
            }
            // ajout reference dans Entity RESERVATION (CPTGP + year + month + ID)
            $lastID = $this->devisRepo->findBy(array(), array('id' => 'DESC'), 1);
            if ($lastID == null) {
                $currentID = 1;
            } else {

                $currentID = $lastID[0]->getId() + 1;
            }
            $devis->setNumeroDevis($currentID);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($devis);
            $entityManager->flush();
            return $devis->getNumero();
        } else {
            return "devisExist";
        }
    }

    /**
     * @Route("/listeclient/", name="client_list", methods={"GET","POST"})
     */
    public function listeclient(Request $request)
    {

        $clients = $this->userRepo->findClients();
        $data = array();
        foreach ($clients as $key => $client) {

            $data[$key]['id'] = $client->getId();
            $data[$key]['nom'] = $client->getNom();
            $data[$key]['prenom'] = $client->getPrenom();
            $data[$key]['email'] = $client->getMail();
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/reservation/session/remove", name="reservationSession_remove", methods={"GET","POST"})
     */
    public function removeSession(Request $request, SessionInterface $session)
    {

        $this->reservationSession->removeReservation();
        return $this->redirectToRoute('step1');
    }

    /**
     * @Route("/vente-comptoir/enregistrer-devis-pdf", name="saveDevis_pdf", methods={"GET","POST"})
     */
    public function saveDevisAsPdf(Request $request): Response
    {

        //extracion mail from string format : "nom prenom (mail)"

        $client = $request->query->get('client');
        $client = explode('(', $client);
        $mailClient = explode(')', $client[1]);
        $mailClient = $mailClient[0];

        //recherche du client correspondant au mail
        $client = $this->userRepo->findOneBy(['mail' => $mailClient]);
        //ajout client dans session
        $this->reservationSession->addClient($client);

        $data = array();
        $dateDepart = $this->reservationSession->getDateDepart();
        $dateRetour = $this->reservationSession->getDateDepart();
        $vehicule = $this->vehiculeRepo->find($this->reservationSession->getVehicule());
        $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);
        $prixGaranties = $this->tarifsHelper->sommeTarifsGaranties($this->garantiesObjectsFromSession());
        $prixOptions = $this->tarifsHelper->sommeTarifsOptions($this->optionsObjectsFromSession());
        $tarifTotal = $tarifVehicule + $prixGaranties + $prixOptions;

        //numero du devis
        $lastID = $this->reservationRepo->findBy(array(), array('id' => 'DESC'), 1);
        $currentID = $lastID[0]->getId() + 1;
        if ($currentID > 100) {
            $numeroDevis = "00" . $currentID;
        } else {

            $numeroDevis = "000" . $currentID;
        }

        $data['dateDepartValue'] = $this->reservationSession->getDateDepart()->format('d/m/Y H:i');
        $data['dateRetourValue'] = $this->reservationSession->getDateRetour()->format('d/m/Y H:i');
        $data['nomClientValue'] = $this->reservationSession->getClient()->getNom();
        $data['prenomClientValue'] = $this->reservationSession->getClient()->getPrenom();
        $data['adresseClientValue'] = $this->reservationSession->getClient()->getAdresse();
        $data['vehiculeValue'] = $vehicule->getMarque() . " " . $vehicule->getModele() . " " . $vehicule->getImmatriculation();
        $data['dureeValue'] = $this->dateHelper->calculDuree($dateDepart, $this->reservationSession->getDateRetour());
        $data['agenceDepartValue'] = $this->reservationSession->getAgenceDepart();
        $data['agenceRetourValue'] = $this->reservationSession->getAgenceRetour();
        $data['numeroDevisValue'] = $numeroDevis;
        $data['tarifValue'] = $tarifTotal;

        return new JsonResponse($data);
    }

    /**
     * @Route("/vente-comptoir/reserver-devis", name="reserver_devis", methods={"GET","POST"})
     */
    public function reserverDevis(Request $request): Response
    {
        // dd($request);
        //extracion mail from string format : "nom prenom (mail)"
        $client = $request->request->get('client');
        $montantPaiement = $request->request->get('montant');
        $client = explode('(', $client);
        $mailClient = explode(')', $client[1]);
        $mailClient = $mailClient[0];

        //recherche du client correspondant au mail
        $client = $this->userRepo->findOneBy(['mail' => $mailClient]);

        $dateDepart = $this->reservationSession->getDateDepart();
        $dateRetour = $this->reservationSession->getDateRetour();
        $vehicule = $this->vehiculeRepo->find($this->reservationSession->getVehicule());

        $reservation = new Reservation();
        $reservation->setVehicule($vehicule);
        $reservation->setClient($client);
        $reservation->setDateDebut($dateDepart);
        $reservation->setDateFin($dateRetour);
        $reservation->setAgenceDepart($this->reservationSession->getAgenceDepart());
        $reservation->setAgenceRetour($this->reservationSession->getAgenceRetour());
        $reservation->setDuree($this->dateHelper->calculDuree($dateDepart, $dateRetour));
        $reservation->setCanceled(false);
        $reservation->setArchived(false);
        //mode reservation
        $reservation->setModeReservation($this->modeReservationRepo->findOneBy(['libelle' => 'CPT']));
        //boucle pour ajout options 
        if ($this->optionsObjectsFromSession() != null) {
            foreach ($this->optionsObjectsFromSession() as $option) {
                $reservation->addOption($option);
            }
        }

        //boucle pour ajout garantie 
        if ($this->garantiesObjectsFromSession() != null) {
            foreach ($this->garantiesObjectsFromSession() as $garantie) {
                $reservation->addGaranty($garantie);
            }
        }

        //si l'admin a entrée un autre tarif dans étape 2, alors on considère ce tarif
        if ($this->reservationSession->getTarifVehicule()) {
            $tarifVehicule = $this->reservationSession->getTarifVehicule();
        } else {
            $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);
        }

        $prixOptions = $this->tarifsHelper->sommeTarifsOptions($this->optionsObjectsFromSession());
        $prixGaranties = $this->tarifsHelper->sommeTarifsGaranties($this->garantiesObjectsFromSession());

        //gestion conducteur optionnel et prix

        if ($this->reservationSession->getConducteur() == "false") {
            $reservation->setConducteur(false);
            $reservation->setPrix($tarifVehicule + $prixOptions + $prixGaranties);
        } else if ($this->reservationSession->getConducteur() == "true") {
            $reservation->setConducteur(true);
            $reservation->setPrix($tarifVehicule + $prixOptions + $prixGaranties + $this->tarifConductSuppl);
        }


        $reservation->setTarifVehicule($tarifVehicule);
        $reservation->setPrixGaranties($this->tarifsHelper->sommeTarifsGaranties($this->garantiesObjectsFromSession()));
        $reservation->setPrixOptions($this->tarifsHelper->sommeTarifsOptions($this->optionsObjectsFromSession()));
        $reservation->setDateReservation($this->dateHelper->dateNow());
        $reservation->setCodeReservation('devisTransformé');
        // ajout reference dans Entity RESERVATION (CPTGP + year + month + ID)
        $lastID = $this->reservationRepo->findBy(array(), array('id' => 'DESC'), 1);
        if ($lastID == null) {
            $currentID = 1;
        } else {
            $currentID = $lastID[0]->getId() + 1;
        }
        //reference réservation CPT ou WEB + année en cours + mois + 0000+ id;
        $reservation->setRefRes($reservation->getModeReservation()->getLibelle(), $currentID);

        $this->em->persist($reservation);
        $this->em->flush();

        // $this->em->flush();
        //on vide la session après reservation et paiement
        $this->reservationSession->removeReservation();

        $this->flashy->success("Réservation effectuée avec succès");
        //envoi de mail de confirmation de réservation



        return $this->redirectToRoute('reservation_index');
    }

    //return an array of objects of options or null
    public function optionsObjectsFromSession()
    {
        //on met dans un tableau les objets corresponans aux options cochés
        $optionsObjects = [];
        if ($this->reservationSession->getOptions() != null) {
            foreach ($this->reservationSession->getOptions() as $opt) {
                array_push($optionsObjects, $this->optionsRepo->find($opt));
            }
            return $optionsObjects;
        } else {
            return null;
        }
    }

    //return an array of objects of garanties
    public function garantiesObjectsFromSession()
    {
        //on met dans un tableau les objets corresponans aux garanties cochés
        $garantiesObjects = [];
        if ($this->reservationSession->getGaranties() != null) {
            foreach ($this->reservationSession->getGaranties() as $gar) {
                array_push($garantiesObjects, $this->garantiesRepo->find($gar));
            }
            return $garantiesObjects;
        } else {
            return null;
        }
    }

    /**
     * @Route("/vente-comptoir/montant-paiement", name="input_montant", methods={"GET","POST"})
     */
    public function loadInputMontant(Request $request): Response
    {
        return $this->render('admin/vente_comptoir2/montant_paiement.html.twig');
    }
}
