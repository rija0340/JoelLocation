<?php

namespace App\Controller\Client;

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
use Doctrine\ORM\EntityManager;
use App\Form\ClientRegisterType;
use App\Repository\UserRepository;
use App\Controller\DevisController;
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
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
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
    private $devisController;
    private $flashy;

    public function __construct(FlashyNotifier $flashy, DevisController $devisController, ConducteurRepository $conductRepo,  UserRepository $userRepo, ReservationController $reservController, DateHelper $dateHelper, TarifsHelper $tarifsHelper, DevisRepository $devisRepo, ReservationRepository $reservRepo, UserPasswordEncoderInterface $passwordEncoder, VehiculeRepository $vehiculeRepository, OptionsRepository $optionsRepo, GarantieRepository $garantiesRepo)
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
        $this->devisController = $devisController;
        $this->flashy = $flashy;
    }

    /**
     * @Route("/espaceclient", name="espaceClient_index")
     */
    public function index(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $client = $this->getUser();
        $notification_pwd = null;

        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }

        $date = new \DateTime('now');
        $message_reservation = '';
        $reservation = new Reservation();
        $mode_reservation = $this->getDoctrine()->getRepository(ModeReservation::class)->findOneBy(['id' => 3]);
        $etat_reservation = $this->getDoctrine()->getRepository(EtatReservation::class)->findOneBy(['id' => 1]);
        $formClient = $this->createForm(ClientType::class, $client);
        $formClientCompte = $this->createForm(ClientCompteType::class, $client);
        // $form->handleRequest($request);
        $formClientCompte->handleRequest($request);

        if ($formClientCompte->isSubmitted() && $formClientCompte->isValid()) {
            //traitement nouveau mot de passe 
            $old_pwd = $formClientCompte->get('old_password')->getData();

            if ($encoder->isPasswordValid($client, $old_pwd)) {

                $new_pwd = $formClientCompte->get('new_password')->getData();
                $password = $encoder->encodePassword($client, $new_pwd);
                $client->setPassword($password);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();
                // $notification_pwd = "Votre mot de passe a bien été mise à jour.";
                //redirection vers logout pour entrer nouveau mot de passe 
                $this->flashy->success('Votre mot de passe a été modifié, veuillez vous connecter à nouveau');

                return $this->redirectToRoute('app_logout');
            } else {

                $this->flashy->error("Votre mot de passe actuel n'est pas le bon");
                $notification_pwd = "Votre mot de passe actuel n'est pas le bon.";
            }
        }

        // page client.html auparavant
        return $this->render('client/index.html.twig', [

            'client' => $client->getUsername(),
            'id' => $client->getId(),
            // 'form' => $form->createView(),
            'formClient' => $formClient->createView(),
            'formClientCompte' => $formClientCompte->createView(),
            'notification_pwd' => $notification_pwd

        ]);
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
     * @Route("/espaceclient/modifier/{id}", name="infoclient_edit", methods={"GET","POST"})
     */
    public function editInfoClient(Request $request, User $user): Response
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

            $this->flashy->success('Votre modification a été enregistré');
            return $this->redirectToRoute('app_logout');
        }

        return $this->render('client/information/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
