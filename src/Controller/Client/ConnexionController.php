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

class ConnexionController extends AbstractController
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
     * @Route("/login_client", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $user = new User();
        $form = $this->createForm(LoginType::class, $user);
        $form->handleRequest($request);

        // Récupérer les erreurs d'authentification
        $error = $authenticationUtils->getLastAuthenticationError();
        // Déterminer le nom d'utilisateur à afficher
        $lastUsername = $authenticationUtils->getLastUsername();
        
        // Déterminer si on doit afficher le bouton de renvoi (for account activation)
        $session = $request->getSession();
        $accountNotActivated = $session->get('account_not_activated', false);
        $emailForActivation = $session->get('email_for_activation', null);
        $tokenInvalid = $session->get('token_invalid', false);
        $tokenExpired = $session->get('token_expired', false);
        $showResend = $accountNotActivated || $tokenInvalid || $tokenExpired;

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
        return $this->render('vitrine/login.html.twig', [ // Ancien template: 'accueil/login.html.twig'
            'controller_name' => 'LoginController',
            'user' => $user,
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
            'show_resend' => $showResend,
            'email_for_activation' => $emailForActivation ?: $lastUsername,
        ]);
    }
}
