<?php

namespace App\Controller\Client;

use App\Classe\Mailjet;
use App\Entity\Devis;
use App\Service\DateHelper;
use App\Service\TarifsHelper;
use App\Form\ReservationStep1Type;
use App\Repository\DevisRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;
use App\Repository\ReservationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Classe\ReservationSession;
use App\Entity\Reservation;
use App\Service\ReservationHelper;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class NouvelleReservationController extends AbstractController
{

    private $reservationRepo;
    private $flashy;
    private $devisRepo;
    private $garantiesRepo;
    private $optionsRepo;
    private $dateHelper;
    private $tarifsHelper;
    private $vehiculeRepo;
    private $reservationHelper;
    private $reservationSession;

    public function __construct(

        ReservationRepository $reservationRepo,
        DevisRepository $devisRepo,
        OptionsRepository $optionsRepo,
        GarantieRepository $garantiesRepo,
        DateHelper $dateHelper,
        TarifsHelper $tarifsHelper,
        VehiculeRepository $vehiculeRepo,
        FlashyNotifier $flashy,
        ReservationHelper $reservationHelper,
        ReservationSession $reservationSession

    ) {
        $this->reservationRepo = $reservationRepo;
        $this->devisRepo = $devisRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->optionsRepo = $optionsRepo;
        $this->dateHelper = $dateHelper;
        $this->tarifsHelper = $tarifsHelper;
        $this->vehiculeRepo = $vehiculeRepo;
        $this->flashy = $flashy;
        $this->reservationHelper = $reservationHelper;
        $this->reservationSession = $reservationSession;
    }
    //step1 choix des paramètres de la réservation

    /**
     * @Route("/espaceclient/nouvelle-reservation/etape1", name="client_step1", methods={"GET","POST"})
     *
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

            return $this->redirectToRoute('client_step2');
        }

        return $this->render('client/nouvelleReservation/step1.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/espaceclient/nouvelle-reservation/etape2", name="client_step2", methods={"GET","POST"})
     */
    public function step2(Request $request, PaginatorInterface $paginator): Response
    {
        $dateDepart = $this->reservationSession->getDateDepart();
        $dateRetour = $this->reservationSession->getDateRetour();

        //un tableau contenant les véhicules utilisées dans les reservations se déroulant entre
        //$dateDepart et $dateRetour choisis dans step1 de la réservation
        $vehiculesReserves = [];
        $reservations = $this->reservationRepo->findReservationIncludeDates($dateDepart, $dateRetour);
        $vehicules = $this->vehiculeRepo->findAll();

        //vehicule disponible en fonction des réservés
        $vehiculesDisponible = $this->reservationHelper->getVehiculesDisponible($reservations);

        //ajout id véhicule dans session, erreur si on stock directement l'objet véhicule dans session
        //un objet vehicule dans session et ensuite on enregistre dans base de données
        if ($request->request->get('vehicule') != null) {

            $tarif = $request->request->get('tarif');
            $id_vehicule = $request->request->get('vehicule');

            //le tarif peut être null si n'est pas encore saisi par l'admin
            if ($tarif != null) {
                $this->reservationSession->addTarifVehicule($tarif);
            }

            $this->reservationSession->addVehicule($id_vehicule);

            return $this->redirectToRoute('client_step3');
        }

        //tarifs des vehicules
        $dateDepart = $this->reservationSession->getDateDepart();
        $dateRetour = $this->reservationSession->getDateRetour();

        $vehicules = $vehiculesDisponible;
        //data contient les tarifs des vehicules disponibles
        $data = [];
        foreach ($vehicules as $key => $veh) {
            $tarif = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $veh);
            $data[$key]['vehicule'] = $veh;
            $data[$key]['tarif'] = $tarif;
        }

        //utilisation de paginator pour liste véhicule disponible
        //pagination
        $vehiculesDisponible = $paginator->paginate(
            $vehiculesDisponible, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            3 // Nombre de résultats par page
        );

        return $this->render('client/nouvelleReservation/step2.html.twig', [
            'vehiculesDisponible' => $vehiculesDisponible,
            'data' => $data,
        ]);
    }

    /**
     * @Route("/espaceclient/nouvelle-reservation/etape3", name="client_step3", methods={"GET","POST"})
     */
    public function step3(Request $request)
    {
        //dd($this->reservationSession->getVehicule());
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
            //ajout conducteur dans session
            $this->reservationSession->addConducteur($conducteur);

            return $this->redirectToRoute('client_step4');
        }

        $dateDepart = $this->reservationSession->getDateDepart();
        $dateRetour = $this->reservationSession->getDateRetour();
        $vehicule = $this->vehiculeRepo->find($this->reservationSession->getVehicule());

        return $this->render('client/nouvelleReservation/step3.html.twig', [

            'options' => $options,
            'garanties' => $garanties,
            'vehicule' => $vehicule,
            'tarifVehicule' => $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule),
            'duree' => $this->dateHelper->calculDuree($this->reservationSession->getDateDepart(), $this->reservationSession->getDateRetour()),
            'agenceDepart' => $this->reservationSession->getAgenceDepart(),
            'dateDepart' => $this->reservationSession->getDateDepart(),
            'agenceRetour' => $this->reservationSession->getAgenceRetour(),
            'dateRetour' => $this->reservationSession->getDateRetour(),

        ]);
    }

    /**
     * @Route("/espaceclient/nouvelle-reservation/etape4", name="client_step4", methods={"GET","POST"})
     */
    public function step4(Request $request)
    {

        //recupération des données venant de sessions pour être affichées dans la page step4
        $dateDepart = $this->reservationSession->getDateDepart();
        $dateRetour = $this->reservationSession->getDateRetour();
        $vehicule = $this->vehiculeRepo->find($this->reservationSession->getVehicule());
        $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);
        $optionsData = $this->reservationSession->getOptions();
        $garantiesData = $this->reservationSession->getGaranties();

        //on met dans un tableau les objets correspondants aux options cochés
        $optionsObjects = [];
        if ($optionsData != null) {
            foreach ($optionsData as $opt) {
                array_push($optionsObjects, $this->optionsRepo->find($opt));
            }
        }

        //on met dans un tableau les objets correspondants aux garanties cochés
        $garantiesObjects = [];
        if ($garantiesData != null) {
            foreach ($garantiesData as $gar) {
                array_push($garantiesObjects, $this->garantiesRepo->find($gar));
            }
        }

        if ($this->reservationSession->getConducteur() == "true") {
            $tarifTotal = 50 +  $this->tarifsHelper->calculTarifTotal($tarifVehicule, $optionsObjects, $garantiesObjects);
        } else if ($this->reservationSession->getConducteur() == "false") {
            $tarifTotal = $this->tarifsHelper->calculTarifTotal($tarifVehicule, $optionsObjects, $garantiesObjects);
        }

        return $this->render('client/nouvelleReservation/step4.html.twig', [

            'vehicule' => $vehicule,
            'tarifVehicule' => $tarifVehicule,
            'duree' => $this->dateHelper->calculDuree($dateDepart, $dateRetour),
            'agenceDepart' => $this->reservationSession->getAgenceDepart(),
            'dateDepart' => $this->reservationSession->getDateDepart(),
            'agenceRetour' => $this->reservationSession->getAgenceRetour(),
            'dateRetour' => $this->reservationSession->getDateRetour(),
            'garanties' => $garantiesObjects,
            'options' => $optionsObjects,
            'conducteur' => $this->reservationSession->getConducteur(),
            'tarifTotal' => $tarifTotal

        ]);
    }


    /**
     * @Route("/espaceclient/nouvelle-reservation/enregistrer-devis/{type}", name="client_saveDevis", methods={"GET","POST"})
     */
    public function saveDevis(Request $request, $type): Response
    {

        $optionsData = $this->reservationSession->getOptions();
        $garantiesData = $this->reservationSession->getGaranties();

        //on met dans un tableau les objets corresponans aux options cochés
        $optionsObjects = [];
        if ($optionsData != null) {
            foreach ($optionsData as $opt) {
                array_push($optionsObjects, $this->optionsRepo->find($opt));
            }
        }

        //on met dans un tableau les objets corresponans aux garanties cochés
        $garantiesObjects = [];
        if ($garantiesData != null) {
            foreach ($garantiesData as $gar) {
                array_push($garantiesObjects, $this->garantiesRepo->find($gar));
            }
        }

        //ajout client dans session
        $this->reservationSession->addClient($this->getUser());

        //enregistrement session dans devis
        $devis = new Devis();

        //utile pour eviter erreur new entity, cette erreur apparait lorsque on utilise directement objet véhicule dans session
        $vehicule = $this->vehiculeRepo->find($this->reservationSession->getVehicule());
        // dd($vehicule);

        $devis->setAgenceDepart($this->reservationSession->getAgenceDepart());
        $devis->setAgenceRetour($this->reservationSession->getAgenceRetour());
        $devis->setDateDepart($this->reservationSession->getDateDepart());
        $devis->setDateRetour($this->reservationSession->getDateRetour());
        $devis->setVehicule($vehicule);
        $devis->setLieuSejour($this->reservationSession->getLieuSejour());
        $devis->setClient($this->reservationSession->getClient());
        $devis->setDateCreation($this->dateHelper->dateNow());
        //si l'heure de la date est égal à zero 
        $devis->setDuree($this->dateHelper->calculDuree($this->reservationSession->getDateDepart(), $this->reservationSession->getDateRetour()));
        $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($this->reservationSession->getDateDepart(), $this->reservationSession->getDateRetour(), $vehicule);
        $devis->setTarifVehicule($tarifVehicule);
        $prixOptions = $this->tarifsHelper->sommeTarifsOptions($optionsObjects);
        $devis->setPrixOptions($prixOptions);
        $prixGaranties = $this->tarifsHelper->sommeTarifsGaranties($garantiesObjects);
        $devis->setPrixGaranties($prixGaranties);

        //gestion conducteur optionnel et prix

        if ($this->reservationSession->getConducteur() == "false") {
            $devis->setConducteur(false);
            $devis->setPrix($tarifVehicule + $prixGaranties + $prixOptions);
        } else if ($this->reservationSession->getConducteur() == "true") {
            $devis->setConducteur(true);
            $devis->setPrix(50 + $tarifVehicule + $prixGaranties + $prixOptions);
        }

        $devis->setTransformed(false);
        //ajout de ID unique dans la base pour pouvoir telecharger par un lien envoyé au client par mail
        $devis->setDownloadId(uniqid());
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

        //avant de sauve le devis , tester s'il existe déjà
        //ceci est nécessaire au cas ou il y a un problème de connexion et que l'email de 
        //confirmation ne peut être envoyer, au cas ou le client rafraichi la page, 
        //le devis pourait être enregistré deux fois
        $allDevis = $this->devisRepo->findAll();
        $devisExiste = false;
        foreach ($allDevis as $dev) {
            if (
                $dev->getClient() == $devis->getClient() &&
                $dev->getDateDepart() == $devis->getDateDepart()
                && $dev->getDateRetour() == $devis->getDateRetour()
                && $dev->getVehicule() == $devis->getVehicule()
            ) {
                $devisExiste = true;
            }
        }

        if ($devisExiste == false) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($devis);
            $entityManager->flush();
        } else {
            $this->flashy->error("Le devis existe déjà");
            return $this->redirectToRoute('client_reservations');
        }

        if ($type == 'devis') {
            $this->flashy->success('Le devis a été enregistré avec succés');
            $this->reservationHelper->sendMailConfirmationDevis($devis);
            return $this->redirectToRoute('client_reservations');
        } elseif ($type == 'reservation') {
            return $this->redirectToRoute('validation_step3', ['id' => $devis->getId()]);
        }
    }
}
