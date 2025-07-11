<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Classe\Mailjet;
use App\Service\DateHelper;
use App\Classe\ReserverDevis;
use App\Service\TarifsHelper;
use App\Repository\UserRepository;
use App\Service\ReservationHelper;
use App\Form\DevisEditVehiculeType;
use App\Repository\DevisRepository;
use App\Repository\OptionsRepository;
use App\Repository\GarantieRepository;
use App\Repository\VehiculeRepository;
use App\Form\EditClientReservationType;
use App\Form\Devis\OptionsGarantiesType;
use App\Repository\ReservationRepository;
use App\Service\SymfonyMailerHelper;
use Doctrine\ORM\EntityManagerInterface;

// Include Dompdf required namespaces
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class DevisController extends AbstractController
{

    private $userRepo;
    private $vehiculeRepo;
    private $devisRepo;
    private $garantiesRepo;
    private $optionsRepo;
    private $tarifsHelper;
    private $dateHelper;
    private $em;
    private $mailjet;
    private $flashy;
    private $reserverDevis;
    private $symfonyMailerHelper;
    private $reservationHelper;
    private $reservationRepo;



    public function __construct(
        FlashyNotifier $flashy,
        Mailjet $mailjet,
        EntityManagerInterface $em,
        DateHelper $dateHelper,
        TarifsHelper $tarifsHelper,
        UserRepository $userRepo,
        DevisRepository $devisRepo,
        VehiculeRepository $vehiculeRepo,
        OptionsRepository $optionsRepo,
        GarantieRepository $garantiesRepo,
        ReserverDevis $reserverDevis,
        SymfonyMailerHelper $symfonyMailerHelper,
        ReservationHelper $reservationHelper,
        ReservationRepository $reservationRepo

    ) {

        $this->vehiculeRepo = $vehiculeRepo;
        $this->userRepo = $userRepo;
        $this->devisRepo = $devisRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->optionsRepo = $optionsRepo;
        $this->dateHelper = $dateHelper;
        $this->tarifsHelper = $tarifsHelper;
        $this->em = $em;
        $this->mailjet = $mailjet;
        $this->flashy = $flashy;
        $this->reserverDevis = $reserverDevis;
        $this->symfonyMailerHelper = $symfonyMailerHelper;
        $this->reservationHelper = $reservationHelper;
        $this->reservationRepo = $reservationRepo;
    }

    /**
     * @Route("/backoffice/devis", name="devis_index")
     */
    public function index(): Response
    {

        $devis = $this->devisRepo->findBy(["transformed" => 0], ["id" => "ASC"]);

        return $this->render('admin/devis/index.html.twig', [
            'devis' => $devis,

        ]);
    }


    /**
     * @Route("/backoffice/devis/new", name="devis_new", methods={"GET","POST"})
     */
    public function newDevis(Request $request): Response
    {

        $idClient =  $request->query->get('idClient');

        if ($request->isXmlHttpRequest() || $idClient != null) {

            $devis = new Devis();

            $arrayOptionsID = [];
            $arrayGarantiesID = [];
            $options = [];
            $garanties = [];

            $agenceDepart = $request->query->get('agenceDepart');
            $agenceRetour = $request->query->get('agenceRetour');
            $lieuSejour = $request->query->get('lieuSejour');
            $dateTimeDepart = $request->query->get('dateTimeDepart');
            $dateTimeRetour = $request->query->get('dateTimeRetour');
            $vehiculeIM = $request->query->get('vehiculeIM');
            $conducteur = $request->query->get('conducteur');
            $arrayOptionsID = (array) $request->query->get('arrayOptionsID');
            $arrayGarantiesID = (array)$request->query->get('arrayGarantiesID');

            $dateDepart = new \DateTime($dateTimeDepart);
            $dateRetour = new \DateTime($dateTimeRetour);

            $vehicule = $this->vehiculeRepo->findByIM($vehiculeIM);
            $client = $this->userRepo->find($idClient);
            $duree = $this->dateHelper->calculDuree($dateDepart, $dateRetour);
            $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);

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
            if ($arrayGarantiesID != []) {

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
            $devis->setTransformed(false);
            $devis->setTarifVehicule($tarifVehicule);

            // ajout reference dans Entity RESERVATION (CPTGP + year + month + ID)
            $lastID = $this->devisRepo->findBy(array(), array('id' => 'DESC'), 1);
            if ($lastID == null) {
                $currentID = 1;
            } else {

                $currentID = $lastID[0]->getId() + 1;
            }
            $devis->setNumeroDevis($currentID);
            // tous les prix sont en TTC
            $prix = $this->tarifsHelper->calculTarifTotal($tarifVehicule, $options, $garanties, $devis->getConducteur());

            $devis->setPrix($prix);

            $emDevis = $this->getDoctrine()->getManager();
            $emDevis->persist($devis);
            $emDevis->flush();

            return $this->redirectToRoute('devis_index');
        }

        $devis = $this->devisRepo->findAll();

        return $this->render('admin/devis/index.html.twig', [
            'devis' => $devis
        ]);
    }


    /**
     * @Route("/backoffice/devis/details/{id}", name="devis_show", methods={"GET"})
     */
    public function show(Devis $devis, Request $request): Response
    {
        //check si vehicule devis est déjà utilisé dans une reservation
        $reservations = $this->reservationRepo->findReservationIncludeDates($devis->getDateDepart(), $devis->getDateRetour());
        $vehiculeIsNotAvailable = $this->reservationHelper->vehiculeIsInvolved($reservations, $devis->getVehicule());
        if ($vehiculeIsNotAvailable) {
            $vehiculesAvailable = $this->reservationHelper->getVehiculesDisponible($reservations);
        } else {
            $vehiculesAvailable = null;
        }
        $fromReserver = $request->query->has('fromReserver');
        return $this->render('admin/devis/details.html.twig', [
            'vehiculeIsNotAvailable' => $vehiculeIsNotAvailable,
            'fromReserver' => $fromReserver,
            'devis' => $devis,
            'hashedId' => sha1($devis->getId())
        ]);
    }

    /**
     * @Route("/espaceclient/devis/{id}", name="client_devis_show", methods={"GET"})
     */
    // public function client_devis_show(Devis $devis): Response
    // {

    //     return $this->render('client/reservation/details.html.twig', [
    //         'devis' => $devis,
    //     ]);
    // }


    /**
     * @Route("/backoffice/devis/{id}/editVehicule", name="devis_edit_vehicule", methods={"GET","POST"})
     */
    public function editVehicule(Request $request, Devis $devis): Response
    {
        $formVehicule = $this->createForm(DevisEditVehiculeType::class, $devis);

        $formVehicule->handleRequest($request);

        if ($formVehicule->isSubmitted() && $formVehicule->isValid()) {


            $dateDepart = $request->request->get('devis_edit_vehicule')['dateDepart'];
            $dateRetour = $request->request->get('devis_edit_vehicule')['dateRetour'];
            $vehicule = $request->request->get('selectVehicule');

            $dateDepart = $this->dateHelper->newDate($dateDepart);
            $dateRetour = $this->dateHelper->newDate($dateRetour);

            $vehicule = $this->vehiculeRepo->find($vehicule);

            $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);
            $tarifTotal = $this->tarifsHelper->calculTarifTotal($tarifVehicule, $devis->getOptions(), $devis->getGaranties(), $devis->getConducteur());

            $devis->setPrix($tarifTotal);
            $devis->setVehicule($vehicule);
            $devis->setDuree($this->dateHelper->calculDuree($dateDepart, $dateRetour));
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('devis_index');
        }

        return $this->render('admin/devis/editVehicule.html.twig', [
            'formVehicule' => $formVehicule->createView(),
            'devis' => $devis
        ]);
    }

    /**
     * @Route("/backoffice/devis/{id}", name="devis_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Devis $devis): Response
    {
        if ($this->isCsrfTokenValid('delete' . $devis->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($devis);
            $entityManager->flush();
        }

        return $this->redirectToRoute('devis_index');
    }

    /**
     * @Route("/backoffice/devis/{id}/reserver", name="devis_reserver", methods={"GET","POST"})
     */
    public function reserver(Request $request, Devis $devis)
    {
        //check si vehicule devis est déjà utilisé dans une reservation
        $reservations = $this->reservationRepo->findReservationIncludeDates($devis->getDateDepart(), $devis->getDateRetour());
        $vehiculeIsNotAvailable = $this->reservationHelper->vehiculeIsInvolved($reservations, $devis->getVehicule());
        if ($vehiculeIsNotAvailable) {
            return $this->redirectToRoute('devis_show', ['id' => $devis->getId(), 'fromReserver' => true]);
            $vehiculesAvailable = $this->reservationHelper->getVehiculesDisponible($reservations);
        } else {
            $vehiculesAvailable = null;
        }

        $this->reserverDevis->reserver($devis, "null", true);

        $this->flashy->success("Le devis" . $devis->getId() . " a été transformé en réservation");
        return $this->redirectToRoute('reservation_index');
    }

    // fonction dans details devis -*/****************************** */

    /**
     *  @Route("/backoffice/devis/modifier-infos-client/{id}", name="devis_infosClient_edit", methods={"GET","POST"},requirements={"id":"\d+"})
     *
     */
    public function editInfosClient(Request $request, Devis $devis): Response
    {
        //form pour client
        $client = $devis->getClient();
        $form = $this->createForm(EditClientReservationType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($client);
            $this->em->flush();
            $this->flashy->success("La réservation a bien été modifié");
            return $this->redirectToRoute('devis_show', ['id' => $devis->getId()]);
        }

        return $this->render('admin/devis/infos_client/edit.html.twig', [

            'form' => $form->createView(),
            'devis' => $devis,

        ]);
    }

    // /**
    //  *  @Route("devis/modifier-options-garanties/{id}", name="devis_optionsGaranties_edit", methods={"GET","POST"},requirements={"id":"\d+"})
    //  */
    // public function editOptionsGaranties(Request $request, Devis $devis, SerializerInterface $serializerInterface): Response
    // {

    //     $form = $this->createForm(OptionsGarantiesType::class, $devis);
    //     $garanties = $this->garantiesRepo->findAll();
    //     $options = $this->optionsRepo->findAll();
    //     $form->handleRequest($request);
    //     //serializer options et garanties de devis
    //     //serializer options et garanties de devis
    //     $dataOptions =  $this->reservationHelper->getOptionsGarantiesAllAndData($devis)["dataOptions"];
    //     $dataGaranties = $this->reservationHelper->getOptionsGarantiesAllAndData($devis)["dataGaranties"];
    //     $allOptions = $this->reservationHelper->getOptionsGarantiesAllAndData($devis)["allOptions"];
    //     $allGaranties = $this->reservationHelper->getOptionsGarantiesAllAndData($devis)["allGaranties"];

    //     if ($request->get('editedOptionsGaranties') == "true") {

    //         $checkboxOptions = $request->get("checkboxOptions");
    //         $checkboxGaranties = $request->get("checkboxGaranties");
    //         $conduteur = $request->get('radio-conducteur');

    //         //changement valeur conducteur
    //         $conducteur = ($conduteur == "true") ? true : false;
    //         $devis->setConducteur($conducteur);
    //         $this->em->flush();

    //         if ($checkboxOptions != []) {
    //             // tous enlever et puis entrer tous les options
    //             foreach ($devis->getOptions() as $option) {
    //                 $devis->removeOption($option);
    //             }
    //             for ($i = 0; $i < count($checkboxOptions); $i++) {
    //                 $devis->addOption($this->optionsRepo->find($checkboxOptions[$i]));
    //             }
    //             $this->em->flush();
    //         } else {
    //             // si il y a des options, les enlever
    //             if (count($devis->getOptions()) > 0) {
    //                 foreach ($devis->getOptions() as $option) {
    //                     $devis->removeOption($option);
    //                 }
    //             }
    //             $this->em->flush();
    //         }

    //         if ($checkboxGaranties != []) {
    //             // tous enlever et puis entrer tous les garanties
    //             foreach ($devis->getGaranties() as $garantie) {
    //                 $devis->removeGaranty($garantie);
    //             }
    //             for ($i = 0; $i < count($checkboxGaranties); $i++) {
    //                 $devis->addGaranty($this->garantiesRepo->find($checkboxGaranties[$i]));
    //             }
    //             $this->em->flush();
    //         } else {
    //             // si il y a des garanties, les enlever
    //             if (count($devis->getGaranties()) > 0) {
    //                 foreach ($devis->getGaranties() as $garantie) {
    //                     $devis->removeGaranty($garantie);
    //                 }
    //             }
    //             $this->em->flush();
    //         }
    //         $devis->setPrixGaranties($this->tarifsHelper->sommeTarifsGaranties($devis->getGaranties()));
    //         $devis->setPrixOptions($this->tarifsHelper->sommeTarifsOptions($devis->getOptions(), $devis->getConducteur()));
    //         $devis->setPrix($devis->getTarifVehicule() + $devis->getPrixOptions() + $devis->getPrixGaranties());

    //         $this->em->flush();
    //         return $this->redirectToRoute('devis_show', ['id' => $devis->getId()]);
    //     }

    //     return $this->render('admin/devis_reservation/options_garanties/edit.html.twig', [
    //         'form' => $form->createView(),
    //         'devis' => $devis,
    //         'garanties' => $garanties,
    //         'options' => $options,
    //         'routeReferer' => 'reservation_show',
    //         'dataOptions' => $dataOptions,
    //         'dataGaranties' => $dataGaranties,
    //         'allOptions' => $allOptions,
    //         'allGaranties' => $allGaranties,
    //         'conducteur' => $devis->getConducteur(),
    //         'type' => 'devis',
    //         'prixConductSuppl' => $this->tarifsHelper->getPrixConducteurSupplementaire()

    //     ]);
    // }




    /**
     * @Route("/backoffice/devis/envoi-identification-connexion/{id}", name="devis_ident_connex", methods={"GET","POST"},requirements={"id":"\d+"})
     */
    public function envoyerIdentConnex(Request $request, Devis $devis, UserPasswordEncoderInterface $passwordEncoder): Response
    {

        $mail = $devis->getClient()->getMail();
        $nom = $devis->getClient()->getNom();
        $mdp = uniqid();
        $content = "Bonjour, " . '<br>' .  "voici vos identifications de connexion." . '<br>' . " Mot de passe: " . $mdp . '<br>' . "Email : votre email";

        $devis->getClient()->setPassword($passwordEncoder->encodePassword(
            $devis->getClient(),
            $mdp
        ));
        $this->em->flush();

        $this->mailjet->send($mail, $nom, "Identifiants de connexion", $content);

        $this->flashy->success("Les identifians de connexion du client ont été envoyés");
        return $this->redirectToRoute('devis_show', ['id' => $devis->getId()]);
    }
    /**
     * @Route("/backoffice/devis/envoyer-devis/{id}", name="envoyer_devis", methods={"GET","POST"},requirements={"id":"\d+"})
     */
    public function envoyerDevis(Request $request, Devis $devis): Response
    {
        $this->symfonyMailerHelper->sendDevis($request, $devis);
        return $this->redirectToRoute('devis_show', ['id' => $devis->getId()]);
    }
}
