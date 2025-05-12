<?php

namespace App\Controller;

use App\Entity\Faq;
use App\Entity\Mail;
use App\Entity\User;
use App\Classe\Mailjet;
use App\Form\LoginType;
use App\Entity\Paiement;
use App\Entity\Vehicule;
use App\Form\ClientType;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Entity\ModePaiement;
use App\Service\SymfonyMailer;
use App\Entity\EtatReservation;
use App\Entity\ModeReservation;
use App\Repository\UserRepository;
use App\Form\FormulaireContactType;
use App\Form\ReservationclientType;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use App\Repository\EtatReservationRepository;
use App\Repository\ModeReservationRepository;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccueilController extends AbstractController
{
    private $passwordEncoder;
    private $vehiculeRepo;
    private $flashy;
    private $em;
    private $dateHelper;
    private $mailjet;

    public function __construct(Mailjet $mailjet, DateHelper $dateHelper, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, VehiculeRepository $vehiculeRepository, FlashyNotifier $flashy)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->vehiculeRepo = $vehiculeRepository;
        $this->flashy = $flashy;
        $this->em = $em;
        $this->dateHelper = $dateHelper;
        $this->mailjet = $mailjet;
    }

    /**
     * @Route("/", name="accueil")
     */
    public function index(): Response
    {

        return $this->render('accueil/index.html.twig', [
            'vehicules' => $this->getUniqueModeleNosVehicules()
        ]);
    }


    /**
     * @Route("/quisommenous", name="quisommenous")
     */
    public function quiSommeNous(): Response
    {
        return $this->render('accueil/quisommenous.html.twig', [
            'controller_name' => 'QuiSommeNousController',
        ]);
    }
    // /**
    //  * @Route("/sendmailtoyahoo", name="sendmailtoyahoo")
    //  */
    // public function sendMailToYahoo(Request $request)
    // {

    //     // $this->mailjet->confirmationDevis(
    //     //     "Rakotoarinelina",
    //     //     "rakotoarinelinarija@hotmail.com",
    //     //     "Confirmation de demande de devis",
    //     //     "07 Janvier 2025",
    //     //     "DV2500206",
    //     //     "RENAULT Clio 5",
    //     //     "08 Janvier 2025 12:00",
    //     //     "21 Janvier 2025 14:00",
    //     //     "https://www.youtube.com/watch?v=-8llPMvdWag",
    //     //     "https://www.youtube.com/watch?v=-8llPMvdWag"
    //     //     //            $this->dateHelper->frenchDate($devis->getDateRetour()->modify('+3 days'))
    //     // );

    //     $this->mailjet->sendCustomEmail("rakotoarinelinarija@yahoo.com", "rija", "envoi de devis", "bonjour ceci est un test");
    // }

    /** 
     * cette fonction retourne unique modele de véhicule selon modeles voulu
     */
    public function getUniqueModeleNosVehicules()
    {
        $vehiculesToDisplay = [];
        $vehicules = $this->vehiculeRepo->findAllVehiculesWithoutVendu();
        $i = 0;
        $modele = ['twingo', 'clio', 'clio 5', 'captur'];

        for ($i = 0; $i < count($modele); $i++) {
            foreach ($vehicules as $key => $vehicule) {

                $nomModele = strtolower($vehicule->getModele()->getLibelle());
                if ($nomModele == $modele[$i]) {
                    array_push($vehiculesToDisplay, $vehicule);
                    break;
                }
            }
        }
        return $vehiculesToDisplay;
    }

    /**
     * @Route("/capture", name="capture")
     */
    public function captur(): Response
    {
        return $this->render('accueil/capture.html.twig', [
            'controller_name' => 'CaptureController',
        ]);
    }


    /**
     * @Route("/clio", name="clio")
     */
    public function clio(): Response
    {
        return $this->render('accueil/clio.html.twig', [
            'controller_name' => 'ClioController',
        ]);
    }


    /**
     * @Route("/twingo", name="twingo")
     */
    public function twingo(): Response
    {
        return $this->render('accueil/twingo.html.twig', [
            'controller_name' => 'TwingoController',
        ]);
    }


    /**
     * @Route("/mentionlegale", name="mentionlegale")
     */
    public function mentionLegale(): Response
    {
        return $this->render('accueil/mentionlegale.html.twig', [
            'controller_name' => 'MentionLegaleController',
        ]);
    }



    /**
     * @Route("/nosvehicules", name="nosvehicules")
     */
    public function noVehicules(): Response
    {

        return $this->render('accueil/nosvehicule.html.twig', [
            'controller_name' => 'AccueilController',
            'vehicules' => $this->getUniqueModeleNosVehicules(),
        ]);
    }


    /**
     * @Route("/notrevehicule{id}", name="notrevehicule")
     */
    public function notreVehicule(int $id): Response
    {

        $vehicule = $this->getDoctrine()->getRepository(Vehicule::class)->findOneBy(['id' => $id]);
        return $this->render('accueil/notrevehicule.html.twig', [
            'controller_name' => 'AccueilController',
            'vehicule' => $vehicule,
        ]);
    }


    /**
     * @Route("/carte", name="carte", methods={"GET","POST"})
     */
    public function carte(Request $request)
    {
        $id = $request->request->get("id");
        return $this->render('accueil/paiement.html.twig', [
            'controller_name' => 'carte bancaire',
            'id' => $id,
        ]);
    }


    /**
     * @Route("/foireauxquestion", name="foireauxquestion")
     */
    public function foireauxquestion(): Response
    {
        $faqs = $this->getDoctrine()->getRepository(Faq::class)->findAll();
        return $this->render('accueil/faq.html.twig', [
            'controller_name' => 'FoireAuxQuestions',
            'faqs' => $faqs,
        ]);
    }


    /**
     * @Route("/cgu", name="cgu")
     */
    public function cgu(): Response
    {
        return $this->render('accueil/cgu.html.twig');
    }


    /**
     * @Route("/formulaire-contact", name="formulaire-contact")
     */
    public function formcontact(Request $request, SymfonyMailer $mailer): Response
    {
        $form = $this->createForm(FormulaireContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $nom = $form->getData()['nom'];
            $email = $form->getData()['email'];
            $telephone = $form->getData()['telephone'];
            $adresse = $form->getData()['adresse'];
            $objet = $form->getData()['objet'];
            $message = $form->getData()['message'];

            //tester si presence de lien hypertexte dans le message envoyé par le client et blocké s'il y en a
            //tester aussi longueur message et envoyer si < 255
            //tester aussi adresse mail no-reply, be pas envoyer si présence 
            if (stristr($message, 'http://') || stristr($message, 'https://')) {
                $status = false;
                $this->flashy->error("Veuillez contacter le service client par téléphone ou WhatsApp si vous avez besoin d'envoyer plus d'éléments dans ce message");
            } else {
                //si inférieur envoyer le message
                if (strlen($message) < 255) {

                    if (substr($email, 0, 7) == "noreply" || substr($email, 0, 7) == "no-repl") {
                        $status = false;
                    } else {
                        // envoyer le mail
                        // $status = $this->mailjet->sendToMe($nom, $email, $telephone, $adresse, $objet, $message);
                        // $this->mailjet->sendToMe($nom, $email, $telephone, $adresse, $objet, $message);

                        // concatenation du bouton avec un url 

                        $btnRepondre = '<a href="mailto:' . $email . ' " style="
                        background-color: red;
                        border: none;
                        color: white;
                        padding: 15px 32px;
                        text-align: center;
                        text-decoration: none;
                        display: inline-block;
                        font-size: 16px;
                        ">Répondre</a>';

                        $response =  $this->mailjet->sendToContacJoelLocation($nom, $email, $telephone, $adresse, $objet, $message, $btnRepondre);
                        //  $template = "accueil/cgu.html.twig";
                        //  $mailer->send($objet, "contact@joellocation@gmail.com", $email, $template, []);
                        $this->flashy->success("Votre email a bien été envoyé");
                    }
                } else {
                    $status = false;
                    $this->flashy->error("Ne pas dépasser 250 caractères pour le message");
                }
            }

            // if ($status) {
            //     $this->flashy->success("Votre email a bien été envoyé");
            // } else {
            //     $this->flashy->error("Votre email n'a pas été envoyé");
            // }
            return $this->redirectToRoute('formulaire-contact');
        }
        return $this->render('accueil/contact.html.twig', [
            'form' => $form->createView()
        ]);
    }

    //cette fonction est appelée dans securitycontroller
    /**
     * @Route("/redirection", name="redirection")
     */
    public function redirection()
    {
        $user = $this->getUser();
        if (in_array("ROLE_CLIENT", $user->getRoles())) {
            $this->flashy->success("Vous êtes bien connecté");
            return $this->redirectToRoute('espaceClient_index');
        }
        if (in_array("ROLE_PERSONNEL", $user->getRoles())) {
            $this->flashy->success("Vous êtes bien connecté");

            return $this->redirectToRoute('admin_index');
        }
        if (in_array("ROLE_ADMIN", $user->getRoles())) {
            $this->flashy->success("Vous êtes bien connecté");

            return $this->redirectToRoute('admin_index');
        }
        if (in_array("ROLE_SUPER_ADMIN", $user->getRoles())) {
            $this->flashy->success("Vous êtes bien connecté");

            return $this->redirectToRoute('admin_index');
        }
        return $this->redirectToRoute('app_logout');
        //return $this->render('accueil/contact.html.twig');
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // Récupérer les informations de session
        $session = $request->getSession();

        // Récupérer les variables de session
        $accountNotActivated = $session->get('account_not_activated', false);
        $emailForActivation = $session->get('email_for_activation', null);
        $tokenInvalid = $session->get('token_invalid', false);
        $tokenExpired = $session->get('token_expired', false);

        // Déterminer si on doit afficher le bouton de renvoi
        $showResend = $accountNotActivated || $tokenInvalid || $tokenExpired;

        // Récupérer les erreurs d'authentification
        $error = $authenticationUtils->getLastAuthenticationError();

        // Déterminer le nom d'utilisateur à afficher
        $lastUsername = $emailForActivation ?: $authenticationUtils->getLastUsername();

        $user = new User();
        $form = $this->createForm(LoginType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Votre logique de traitement du formulaire
        }

        return $this->render('accueil/login.html.twig', [
            'controller_name' => 'LoginController',
            'user' => $user,
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
            'show_resend' => $showResend,
            'email_for_activation' => $emailForActivation ?: $lastUsername
        ]);
    }
}
