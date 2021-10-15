<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Entity\Devis;
use App\Entity\Tarifs;
use GuzzleHttp\Client;
use App\Service\DateHelper;
use App\Service\TarifsHelper;
use App\Classe\ReservationSession;
use App\Form\ReservationStep1Type;
use App\Repository\UserRepository;
use App\Form\ClientNewComptoirType;
use App\Form\Step4SelectClientType;
use App\Repository\DevisRepository;
use App\Repository\MarqueRepository;
use App\Repository\ModeleRepository;
use App\Repository\TarifsRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use App\Service\ReservationHelper;
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

    public function __construct(
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
        Mail $mail,
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
    }

    /**
     * @Route("/vente-comptoir/etape1", name="step1", methods={"GET","POST"})
     */
    public function step1(Request $request, SessionInterface $session): Response
    {

        //remove contenu session avant toute chose
        $this->reservationSession->removeReservation();

        $form = $this->createForm(ReservationStep1Type::class);

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

            return $this->redirectToRoute('step2');
        }

        return $this->render('admin/test/step1.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/vente-comptoir/etape2", name="step2", methods={"GET","POST"})
     */
    public function step2(Request $request, PaginatorInterface $paginator): Response
    {

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
        $vehiculesDisponible = $paginator->paginate(
            $vehiculesDisponible, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            3 // Nombre de résultats par page
        );

        return $this->render('admin/test/step2.html.twig', [
            'vehiculesDisponible' => $vehiculesDisponible,
            'data' => $data,
            'dateDepart' => $this->reservationSession->getDateDepart(),
            'dateRetour' => $this->reservationSession->getDateRetour()
        ]);
    }

    /**
     * @Route("/vente-comptoir/etape3", name="step3", methods={"GET","POST"})
     */
    public function step3(Request $request)
    {
        //recupérer liste options et  garanties dans base de données
        $options = $this->optionsRepo->findAll();
        $garanties = $this->garantiesRepo->findAll();

        // recuperation donnée from formulaire options et garanties
        if ($request->request->get('checkboxOptions') != null) {

            //$optionsData et garantiesData sont des tableaux 
            //(mettre un "[]" apres les noms des input type checkbox dans templates pour obtenir tous les  checkbox cochés)
            $conducteur = $request->request->get('radio-conducteur');
            $optionsData = $request->request->get('checkboxOptions');
            $garantiesData = $request->request->get('checkboxGaranties');

            //ajout options et garanties (tableau d'objets) dans session 
            $this->reservationSession->addOptions($optionsData);
            $this->reservationSession->addGaranties($garantiesData);

            return $this->redirectToRoute('step4');
        }


        $dateDepart = $this->reservationSession->getDateDepart();
        $dateRetour = $this->reservationSession->getDateRetour();
        $vehicule =  $this->vehiculeRepo->find($this->reservationSession->getVehicule());

        //si l'admin a entrée un autre tarif dans étape 2, alors on considère ce tarif
        if ($this->reservationSession->getTarifVehicule()) {
            $tarifVehicule = $this->reservationSession->getTarifVehicule();
        } else {
            $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);
        }
        return $this->render('admin/test/step3.html.twig', [

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
        //on met dans un tableau les objets corresponans aux options cochés
        $optionsObjects = [];
        foreach ($this->reservationSession->getOptions() as $opt) {
            array_push($optionsObjects,  $this->optionsRepo->find($opt));
        }

        //on met dans un tableau les objets corresponans aux garanties cochés
        $garantiesObjects = [];
        foreach ($this->reservationSession->getGaranties() as $gar) {
            array_push($garantiesObjects,  $this->garantiesRepo->find($gar));
        }

        $dateDepart = $this->reservationSession->getDateDepart();
        $dateRetour = $this->reservationSession->getDateRetour();
        $vehicule =  $this->vehiculeRepo->find($this->reservationSession->getVehicule());

        //si l'admin a entrée un autre tarif dans étape 2, alors on considère ce tarif
        if ($this->reservationSession->getTarifVehicule()) {
            $tarifVehicule = $this->reservationSession->getTarifVehicule();
        } else {
            $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);
        }

        return $this->render('admin/test/step4.html.twig', [

            'form' => $form->createView(),
            'vehicule' => $vehicule,
            'tarifVehicule' => $tarifVehicule,
            'duree' => $this->dateHelper->calculDuree($dateDepart, $dateRetour),
            'agenceDepart' => $this->reservationSession->getAgenceDepart(),
            'dateDepart' => $this->reservationSession->getDateDepart(),
            'agenceRetour' => $this->reservationSession->getAgenceRetour(),
            'dateRetour' => $this->reservationSession->getDateRetour(),
            'options' => $optionsObjects,
            'garanties' => $garantiesObjects

        ]);
    }

    //enregistrement de devis dans base de données sans envoi mail au client
    /**
     * @Route("/vente-comptoir/enregistrer-devis", name="save_only_devis", methods={"GET","POST"})
     */
    public function saveOnlyDevis(Request $request): Response
    {
        $numDevis = $this->saveDevis($request);

        $this->flashy->success('Le devis numero ' . $numDevis . 'a été enregistré avec succés');
        return $this->redirectToRoute('devis_index');
    }

    //enregistrement de devis dans base de données sans envoi mail au client
    /**
     * @Route("/vente-comptoir/enregistrer-devis-envoi-mail", name="save_devis_send_mail", methods={"GET","POST"})
     */
    public function saveDevisSendMail(Request $request): Response
    {
        $numDevis = $this->saveDevis($request);
        $Mailcontent = 'Un devis a été enregistré, veuillez vous connecter pour le consulter et le valider';
        $this->mail->send($this->reservationSession->getClient()->getMail(), $this->reservationSession->getClient()->getNom(), 'Devis', $Mailcontent);

        $this->flashy->success('Le devis a été enregistré avec succés et un mail a été envoyé au client');
        return $this->redirectToRoute('devis_index');
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

        //enregistrement session dans devis
        $devis = new Devis();

        //utile pour eviter erreur new entity, cette erreur apparait lorsque on utilise directement objet véhicule dans session
        $vehicule = $this->vehiculeRepo->find($this->reservationSession->getVehicule());
        // dd($vehicule);
        //trouver les options et garanties à l'aide des ID 
        //on met dans un tableau les objets corresponans aux options cochés
        $optionsObjects = [];
        foreach ($this->reservationSession->getOptions() as $opt) {
            array_push($optionsObjects,  $this->optionsRepo->find($opt));
        }

        //on met dans un tableau les objets corresponans aux garanties cochés
        $garantiesObjects = [];
        foreach ($this->reservationSession->getGaranties() as $gar) {
            array_push($garantiesObjects,  $this->garantiesRepo->find($gar));
        }

        $devis->setAgenceDepart($this->reservationSession->getAgenceDepart());
        $devis->setAgenceRetour($this->reservationSession->getAgenceRetour());
        $devis->setDateDepart($this->reservationSession->getDateDepart());
        $devis->setDateRetour($this->reservationSession->getDateRetour());
        $devis->setVehicule($vehicule);
        $devis->setLieuSejour($this->reservationSession->getLieuSejour());
        $devis->setClient($this->reservationSession->getClient());
        $devis->setDateCreation($this->dateHelper->dateNow());
        if (date("H", $this->reservationSession->getDateRetour()->getTimestamp()) == 0) {
            $devis->setDuree(ceil(1 + (($this->reservationSession->getDateRetour()->getTimestamp() - $this->reservationSession->getDateDepart()->getTimestamp()) / 60 / 60 / 24)));
        } else {
            $devis->setDuree(ceil((($this->reservationSession->getDateRetour()->getTimestamp() - $this->reservationSession->getDateDepart()->getTimestamp()) / 60 / 60 / 24)));
        }

        // si l'admin a entrée un autre tarif dans étape 2, alors on considère ce tarif
        if ($this->reservationSession->getTarifVehicule()) {
            $tarifVehicule = $this->reservationSession->getTarifVehicule();
        } else {
            $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($this->reservationSession->getDateDepart(), $this->reservationSession->getDateRetour(), $vehicule);
        }
        $devis->setTarifVehicule($tarifVehicule);
        $prixOptions = $this->tarifsHelper->sommeTarifsOptions($optionsObjects);
        $devis->setPrixOptions($prixOptions);
        $prixGaranties = $this->tarifsHelper->sommeTarifsGaranties($garantiesObjects);
        $devis->setPrixGaranties($prixGaranties);
        $devis->setPrix($tarifVehicule + $prixGaranties + $prixOptions);
        $devis->setConducteur(true);
        $devis->setTransformed(false);


        //options et garanties sont des tableaux d'objet dans session
        foreach ($optionsObjects as $option) {
            $devis->addOption($option);
        }
        foreach ($garantiesObjects as $garantie) {
            $devis->addGaranty($garantie);
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
}
