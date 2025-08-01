<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\DateHelper;
use App\Service\TarifsHelper;
use App\Classe\ReservationSession;
use App\Classe\ReserverDevis;
use App\Form\ReservationStep1Type;
use App\Repository\UserRepository;
use App\Service\ReservationHelper;
use App\Form\ClientNewComptoirType;
use App\Repository\DevisOptionRepository;
use App\Repository\DevisRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\ModeReservationRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use App\Service\SymfonyMailerHelper;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class VenteComptoirController extends AbstractController
{

    private $userRepo;
    private $reservationRepo;
    private $vehiculeRepo;
    private $optionsRepo;
    private $garantiesRepo;
    private $dateHelper;
    private $tarifsHelper;
    private $em;
    private $flashy;
    private $passwordEncoder;
    private $devisRepo;
    private $reservationSession;
    private $reservationHelper;
    private $symfonyMailerHelper;
    private $reserverDevis;

    public function __construct(
        ModeReservationRepository $modeReservationRepo,
        FlashyNotifier $flashy,
        EntityManagerInterface $em,
        TarifsHelper $tarifsHelper,
        DateHelper $dateHelper,
        ReservationRepository $reservationRepo,
        UserRepository $userRepo,
        VehiculeRepository $vehiculeRepo,
        OptionsRepository $optionsRepo,
        GarantieRepository $garantiesRepo,
        UserPasswordEncoderInterface $passwordEncoder,
        DevisRepository $devisRepo,
        ReservationSession $reservationSession,
        ReservationHelper $reservationHelper,
        SymfonyMailerHelper $symfonyMailerHelper,
        DevisOptionRepository $devisOptionRepo,
        ReserverDevis $reserverDevis
    ) {

        $this->reservationSession = $reservationSession;
        $this->flashy = $flashy;
        $this->reservationRepo = $reservationRepo;
        $this->vehiculeRepo = $vehiculeRepo;
        $this->optionsRepo = $optionsRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->userRepo = $userRepo;
        $this->dateHelper = $dateHelper;
        $this->tarifsHelper = $tarifsHelper;
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->devisRepo = $devisRepo;
        $this->reservationHelper = $reservationHelper;
        $this->modeReservationRepo = $modeReservationRepo;
        $this->symfonyMailerHelper = $symfonyMailerHelper;
        $this->devisOptionRepo = $devisOptionRepo;
        $this->reserverDevis = $reserverDevis;
    }

    /**
     * @Route("/backoffice/vente-comptoir/etape1", name="step1", methods={"GET","POST"})
     * @Route("/espaceclient/nouvelle-reservation/etape1", name="client_step1", methods={"GET","POST"})
     */
    public function step1(Request $request, SessionInterface $session): Response
    {
        //remove contenu session avant toute chose
        $routeName = $this->get('request_stack')->getCurrentRequest()->get('_route');


        if (!str_contains($request->headers->get('referer'), 'etape2')) {
            $this->reservationSession->removeReservation();
        }

        $form = $this->createForm(ReservationStep1Type::class);

        $devis = $this->reservationHelper->createDevisFromResaSession($this->reservationSession);
        //set data if exists in session 
        $formData = [
            'agenceDepart' => $devis->getAgenceDepart(),
            'dateDepart' => $devis->getDateDepart(),
            'typeVehicule' => 'classic',
            'agenceRetour' => $devis->getAgenceRetour(),
            'dateRetour' => $devis->getDateRetour(),
            'lieuSejour' => $devis->getLieuSejour()
        ];

        if ($this->checkFormDataExists($formData)) {
            $form->setData($formData);
        }

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

            if ($routeName === 'client_step1') {
                return $this->redirectToRoute('client_step2');
            }
            return $this->redirectToRoute('step2');
        }

        $template = $routeName === 'client_step1'
            ? 'client2/reservation/nouvelle_resa/step1.html.twig'
            : 'admin/vente_comptoir2/step1.html.twig';

        return $this->render($template, [
            'form' => $form->createView()
        ]);
    }

    private function checkFormDataExists(array $data): bool
    {
        $requiredFields = ['agenceDepart', 'dateDepart', 'typeVehicule', 'agenceRetour', 'dateRetour'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || is_null($data[$field])) {
                return false;
            }
        }

        return true;
    }


    /**
     * @Route("/backoffice/vente-comptoir/etape2", name="step2", methods={"GET","POST"})
     * @Route("/espaceclient/nouvelle-reservation/etape2", name="client_step2", methods={"GET","POST"})
     */
    public function step2(Request $request, PaginatorInterface $paginator): Response
    {
        $routeName = $this->get('request_stack')->getCurrentRequest()->get('_route');

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

            $tarifVehiculeJournalier = $request->request->get('tarifVehiculeJournalier');

            //si les deux sont renseignés, on prend le tarif journalier
            if ($tarifVehiculeJournalier != null) {
                $duree =  $this->dateHelper->calculDuree($dateDepart, $dateRetour);

                if (!is_null($duree)) {
                    $tarifVehicule = $tarifVehiculeJournalier * $duree;
                }
            }

            $id_vehicule = $request->request->get('vehicule');

            if ($tarifVehicule != null) {
                $this->reservationSession->addTarifVehicule($tarifVehicule);
            } else {
                $this->reservationSession->addTarifVehicule(null);
            }
            $this->reservationSession->addVehicule($id_vehicule);

            if ($routeName === 'client_step2') {
                return $this->redirectToRoute('client_step3');
            }

            return $this->redirectToRoute('step3');
        }

        $data = [];
        foreach ($vehiculesDisponible as $key => $veh) {
            $tarif = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $veh);
            $data[$key]['vehicule'] = $veh;
            $data[$key]['tarif'] = $tarif;
        }

        //utilisation de paginator pour liste véhicule disponible
        //pagination
        $vehiculesDisponiblePagination = $paginator->paginate(
            $vehiculesDisponible, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            3 // Nombre de résultats par page
        );

        $template = $routeName === 'client_step2'
            ? 'client2/reservation/nouvelle_resa/step2.html.twig'
            : 'admin/vente_comptoir2/step2.html.twig';

        $devis = $this->reservationHelper->createDevisFromResaSession($this->reservationSession);
        return $this->render($template, [
            'vehiculesDisponiblePagination' => $vehiculesDisponiblePagination,
            'vehiculesDisponible' => $vehiculesDisponible,
            'data' => $data,
            'dateDepart' => $dateDepart,
            'dateRetour' => $dateRetour,
            'devis' => $devis
        ]);
    }

    /**
     * @Route("/backoffice/vente-comptoir/etape3", name="step3", methods={"GET","POST"})
     * @Route("/espaceclient/nouvelle-reservation/etape3", name="client_step3", methods={"GET","POST"})
     */
    public function step3(Request $request)
    {
        $routeName = $this->get('request_stack')->getCurrentRequest()->get('_route');
        if ($this->reservationSession->getReservation() == null) {
            return $this->redirectToRoute('step1');
        }
        //recupérer liste options et  garanties dans base de données
        $allOptions = $this->optionsRepo->findAll();
        $allGaranties = $this->garantiesRepo->findAll();

        // recuperation donnée from formulaire options et garanties
        if ($request->request->get('radio-conducteur') != null) {

            //$optionsData et garantiesData sont des tableaux 
            //(mettre un "[]" apres les noms des input type checkbox dans templates pour obtenir tous les  checkbox cochés)
            $conducteur = $request->request->get('radio-conducteur');
            //options peut être null

            //ajout options et garanties (tableau d'objets) dans session 
            $this->reservationSession->addOptions($this->reservationHelper->getOptionsFromRequest($request));

            if ($request->get('checkboxGaranties') != null) {
                $garantiesData = $request->request->get('checkboxGaranties');
            }

            if ($request->get('checkboxGaranties') != null) {
                $this->reservationSession->addGaranties($garantiesData);
            }
            $this->reservationSession->addConducteur($conducteur);

            $this->reservationHelper->getOptionsFromRequest($request);

            if ($routeName === 'client_step3') {
                return $this->redirectToRoute('client_step4');
            }

            return $this->redirectToRoute('step4');
        }

        $devis = $this->reservationHelper->createDevisFromResaSession($this->reservationSession);

        $template = $routeName === 'client_step3'
            ? 'client2/reservation/nouvelle_resa/step3.html.twig'
            : 'admin/vente_comptoir2/step3.html.twig';

        return $this->render($template, [

            'allOptions' => $allOptions,
            'allGaranties' => $allGaranties,
            'dataGaranties' => $this->reservationHelper->getGarantiesIds($devis),
            'dataOptions' => $this->reservationHelper->getOptionsIds($devis),
            'devis' => $devis,
            'prixConductSuppl' => $this->tarifsHelper->getPrixConducteurSupplementaire(),
        ]);
    }


    /**
     * @Route("/backoffice/vente-comptoir/etape4", name="step4", methods={"GET","POST"})
     * @Route("/espaceclient/nouvelle-reservation/etape4", name="client_step4", methods={"GET","POST"})
     */

    public function step4(Request $request): Response
    {

        $routeName = $this->get('request_stack')->getCurrentRequest()->get('_route');
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

                if ($routeName === 'client_step4') {
                    return $this->redirectToRoute('client_step4');
                }

                return $this->redirectToRoute('step4');
            }
        }

        $template = $routeName === 'client_step4'
            ? 'client2/reservation/nouvelle_resa/step4.html.twig'
            : 'admin/vente_comptoir2/step4.html.twig';

        $devis = $this->reservationHelper->createDevisFromResaSession($this->reservationSession);

        return $this->render($template, [

            'form' => $form->createView(),
            'devis' => $devis,
            'prixConductSuppl' => $this->tarifsHelper->getPrixConducteurSupplementaire(),

        ]);
    }



    //enregistrement de devis dans base de données sans envoi mail au client

    /**
     * @Route("/backoffice/vente-comptoir/enregistrer-devis", name="save_only_devis", methods={"GET","POST"})
     * @Route("/espaceclient/nouvelle-reservation/enregistrer-devis", name="client_saveDevis", methods={"GET","POST"})
     */
    public function saveOnlyDevis(Request $request): Response
    {

        $client = $this->getClient($request);
        // check if a similar devis already exists
        $result = $this->saveDevis($client);
        if ($result != "devisExist") {
            $numDevis = $result;
            $client = null;

            $routeName = $request->attributes->get('_route');
            
            // dd($routeName);
            if (strpos($routeName, 'client') !== false) {
                // dd('ato izy madalo');
                $devis = $this->devisRepo->findOneBy(['numero' => $numDevis]);
                $this->flashy->success('Le devis a été enregistré avec succés');
                $this->symfonyMailerHelper->sendDevis($request, $devis);

                // redirect to client_reservations with fragment "#details"
                $url = $this->generateUrl('client_reservations') . '#avenir';
                return $this->redirect($url);
                // return $this->redirectToRoute('client_reservations');
            } else {
                $this->flashy->success('Le detailsdevis numero ' . $numDevis . ' a été enregistré avec succés');
                return $this->redirectToRoute('devis_index');
            }
        } else {
            $this->flashy->error("Un devis similaire existe déjà !");
            return $this->redirectToRoute('devis_index');
        }
    }

    public function getClient($request)
    {
        $routeName = $request->attributes->get('_route');
        //is this condition correct  strpos($routeName, 'client')

        if (strpos($routeName, 'client') !== false) {
            $client = $this->getUser();
        } else {
            // //extracion mail from string format : "nom prenom (mail)"
            $client = $request->request->get('client');
            $client = explode('(', $client);
            $mailClient = explode(')', $client[1]);
            $mailClient = $mailClient[0];

            //recherche du client correspondant au mail
            $client = $this->userRepo->findOneBy(['mail' => $mailClient]);
        }

        return $client;
    }

    //enregistrement de devis dans base de données sans envoi mail au client

    /**
     * @Route("/backoffice/vente-comptoir/enregistrer-devis-envoi-mail", name="save_devis_send_mail", methods={"GET","POST"})
     */
    public function saveDevisSendMail(Request $request): Response
    {
        $client  = $this->getClient($request);
        $result = $this->saveDevis($client);

        if ($result != "devisExist") {
            $numDevis = $result;
            //url de téléchargement du devis
            $devis = $this->devisRepo->findOneBy(['numero' => $numDevis]);
            // $this->reservationHelper->sendMailConfirmationDevis($devis, $request);
            $this->symfonyMailerHelper->sendDevis($request, $devis);
            // $this->flashy->success('Le devis a été enregistré avec succés et un mail a été envoyé au client');
            return $this->redirectToRoute('devis_index');
        } else {
            $this->flashy->error("Un devis similaire existe déjà !");
            return $this->redirectToRoute('devis_index');
        }
    }

    public function saveDevis(User $client)
    {

        //ajout client dans session
        $this->reservationSession->addClient($client);

        //utile pour eviter erreur new entity, cette erreur apparait lorsque on utilise directement objet véhicule dans session
        $vehicule = $this->vehiculeRepo->find($this->reservationSession->getVehicule());

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
            $devis = $this->reservationHelper->createDevisFromResaSession($this->reservationSession);
            $devis->setClient($this->reservationSession->getClient());

            // ajout reference dans Entity RESERVATION (CPTGP + year + month + ID)
            $lastID = $this->devisRepo->findBy(array(), array('id' => 'DESC'), 1);
            if ($lastID == null) {
                $currentID = 1;
            } else {

                $currentID = $lastID[0]->getId() + 1;
            }
            $devis->setNumeroDevis($currentID);

            //----------------test---------------------
            //supprimer les devisoptions avant enregistrement du devis dans le bdd 
            foreach ($devis->getDevisOptions() as $option) {
                $devis->removeDevisOption($option);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($devis);
            $entityManager->flush();

            //ajouter les options pour le devis enregistrer dans le bdd
            if ($this->reservationSession->getOptions() != []) {
                //save devis options
                $devis = $this->reservationHelper->saveDevisOptions($devis, $this->reservationSession->getOptions(), $this->em);
            }
            return $devis->getNumero();
        } else {
            return "devisExist";
        }
    }

    /**
     * @Route("/backoffice/listeclient/", name="client_list", methods={"GET","POST"})
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
     * @Route("/backoffice/reservation/session/remove", name="reservationSession_remove", methods={"GET","POST"})
     */
    public function removeSession(Request $request, SessionInterface $session)
    {

        $this->reservationSession->removeReservation();
        return $this->redirectToRoute('step1');
    }

    /**
     * @Route("/backoffice/vente-comptoir/enregistrer-devis-pdf", name="saveDevis_pdf", methods={"GET","POST"})
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
        $prixGaranties = $this->tarifsHelper->sommeTarifsGaranties($this->reservationHelper->garantiesObjectsFromSession($this->reservationSession));

        $conducteur = $this->reservationSession->getConducteur() == "true" ? true : false;

        $prixOptions = $this->tarifsHelper->sommeTarifsOptions($this->reservationHelper->optionsObjectsFromSession($this->reservationSession), $conducteur);
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
     * @Route("/backoffice/vente-comptoir/reserver-devis", name="reserver_devis", methods={"GET","POST"})
     * @Route("/espaceclient/nouvelle-reservation/reserver-devis", name="client_reserverDevis", methods={"GET","POST"})
     */
    public function reserverDevis(Request $request): Response
    {
        $routeName = $request->attributes->get('_route');
        $client = null;

        $client = $this->getClient($request);
        $devis = $this->reservationHelper->createDevisFromResaSession($this->reservationSession);
        $devis->setClient($client);
        //si resrver directement depuis espace client, on flush le devis et on redirect vers payment
        if ($routeName === 'client_reserverDevis') {

            $numDevis = $this->saveDevis($client);
            $devis = $this->devisRepo->findOneBy(['numero' => $numDevis]);

            return $this->redirectToRoute('validation_step3', ['id' => $devis->getId()]);
        }
        $reservation = $this->reserverDevis->reserver($devis, "null", false);
        //on vide la session après reservation et paiement
        $this->reservationSession->removeReservation();

        $this->flashy->success("Réservation effectuée avec succès");
        //envoi de mail de confirmation de réservation

        return $this->redirectToRoute('reservation_index');
    }

    /**
     * @Route("/backoffice/vente-comptoir/montant-paiement", name="input_montant", methods={"GET","POST"})
     */
    public function loadInputMontant(Request $request): Response
    {
        return $this->render('admin/vente_comptoir2/montant_paiement.html.twig');
    }
}
