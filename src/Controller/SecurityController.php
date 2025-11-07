<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Service\EmailManagerService;
use App\Form\LoginType;

/*
 * Ancien template: @templates/accueil/login.html.twig
 *
 * {% extends 'base.html.twig' %}
 *
 * {% block title %}login
 * {% endblock %}
 *
 * {% block body %}
 * 	<div class="container">
 * 		{{ form_start(form) }}
 * 		{{ form_widget(form) }}
 * 		<button class="button">{{ button_label|default('Connecter') }}</button>&nbsp;&nbsp;&nbsp;<a href="{{ path('inscription') }}" class="button">S'inscrire</a>
 * 		{{ form_end(form) }}
 * 		<br>
 * 	</div>
 * {% endblock %}
 */

class SecurityController extends AbstractController
{
    private $flashy;
    private $session;
    private $entityManager;
    private $emailManagerService;
    private $eventDispatcher;
    private $userRepo;

    public function __construct(
        SessionInterface $session,
        FlashyNotifier $flashy,
        EntityManagerInterface $entityManager,
        EmailManagerService $emailManagerService,
        EventDispatcherInterface $eventDispatcher,
        UserRepository $userRepo
    ) {
        $this->flashy = $flashy;
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->emailManagerService = $emailManagerService;
        $this->eventDispatcher = $eventDispatcher;
        $this->userRepo = $userRepo;
    }

    /**
     * @Route("/connexion", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // Vérification si l'utilisateur est déjà connecté
        $clientMail = "";
        $show_resend = false;

        // Récupérer les informations de session
        $session = $request->getSession();
        $fromInvalidToken = $session->get('token_invalid');
        $fromEmailSent = $session->get('from_email_sent');
        $fromEmailSentRecently = $session->get('from_email_sent_recently');
        $fromNotActivatedAccount = $session->get('account_not_activated');
        $fromActivatedAccount = $session->get('form_validated_account');
        $fromInscriptionSession = $session->get('from_inscription_session');
        
        // Check if there's an ongoing reservation in session
        $reservationData = $session->get('reservation');
        $hasOngoingReservation = $reservationData && !empty($reservationData);

        //check si sessiondata n'est pas null ?
        if ($fromNotActivatedAccount) {
            $this->addFlash(
                'warning',
                'Votre compte n\'est pas encore activé ! Vérifiez vos mails pour activer votre compte.'
            );
            $show_resend = true;
            //remove session 
            $session->remove('account_not_activated');
        }
        if ($fromInvalidToken) {
            $this->addFlash(
                'danger',
                'Votre token n\'est pas valide, veuillez clicker sur le bouton "Renvoyer le mail de validation" pour recevoir un nouveau mail de validation.'
            );
            $show_resend = true;
            //remove session 
            $session->remove('token_invalid');
        }
        if ($fromEmailSentRecently) {
            $this->addFlash(
                'warning',
                'Un email a déjà été envoyé récemment. Veuillez attendre quelques minutes avant de réessayer.'
            );
            $show_resend = true;
            //remove session 
            $session->remove('from_email_sent_recently');
        }
        if ($fromEmailSent) {
            $this->addFlash(
                'success',
                'Un email de validation vient de vous être envoyé. Veuillez cliquer sur le lien de validation.'
            );
            $show_resend = true;
            $session->remove('from_email_sent');
        }

        if ($fromInscriptionSession) {
            $this->addFlash(
                'success',
                'Merci pour votre inscription ! Un e-mail de validation vient de vous être envoyé.'
            );
            $session->remove('from_inscription_session');
        }
        if ($fromActivatedAccount) {
            $this->addFlash(
                'success',
                'Votre compte a été activé avec succès. Veuillez vous connecter.'
            );
            $session->remove('form_validated_account');
        }
        
        // Add flash message if there's an ongoing reservation
        // Only show this if the user is not coming from inscription session to avoid duplicate messages
        if ($hasOngoingReservation && !$fromInscriptionSession) {
            $this->addFlash(
                'info',
                'Vous avez une réservation en cours. Connectez-vous ou créez un compte pour finaliser votre réservation.'
            );
        }

        // account_not_activated
        $error = $authenticationUtils->getLastAuthenticationError();
        // Déterminer le nom d'utilisateur à afficher
        $lastUsername = $clientMail ?: $authenticationUtils->getLastUsername();
        if ($error) {
            $this->addFlash(
                'danger',
                $error->getMessage()
            );
        }
        
        // User instance for template context
        $user = new User();
        
        //si le compte n'est pas encore activé 
        // if ($lastUsername == !"") {
        //     $user  = $this->userRepo->findOneBy(['mail' => $lastUsername]);
        //     if ($user && !$user->getPresence() && !$fromInscription && !$fromInvalidToken && !$fromEmailSent && !$fromEmailSentRecently) {
        //         dump($fromInvalidToken);
        //         $this->addFlash(
        //             'warning',
        //             'Votre compte n\'est pas encore activé ! Vérifiez vos mails pour activer votre compte.'
        //         );
        //     }
        // }
        
        // Afficher la page de login
        return $this->render('vitrine/login.html.twig', [ // Ancien template: 'security/login.html.twig'
            'last_username' => $lastUsername,
            'error' => $error,
            'show_resend' => $show_resend,
            'user' => $user,
            'email_for_activation' => $lastUsername,
            'controller_name' => 'SecurityController',
        ]);
    }

    /**
     * @Route("/renvoyer-validation", name="resend_validation_email", methods={"POST"})
     */
    public function resendValidationEmail(Request $request): Response
    {
        $email = $request->request->get('email');
        if (!$email) {
            return $this->redirectToRoute('app_login', [
                'error' => urlencode('Adresse email manquante.')
            ]);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['mail' => $email]);

        if (!$user) {
            return $this->redirectToRoute('app_login', [
                'error' => urlencode('Aucun compte trouvé avec cette adresse email.')
            ]);
        }

        if ($user->getPresence()) {
            return $this->redirectToRoute('app_login', [
                'info' => urlencode('Votre compte est déjà validé. Vous pouvez vous connecter.')
            ]);
        }

        // Vérifier si un email a déjà été envoyé récemment en utilisant date_inscription
        $dateInscription = $user->getDateInscription();
        $now = new \DateTime();
        $interval = $now->diff($dateInscription);

        // Si la date d'inscription a été mise à jour il y a moins de 5 minutes
        if ($interval->days === 0 && $interval->h === 0 && $interval->i < 5) {
            // Rediriger immédiatement
            $session = $request->getSession();
            $session->set('from_email_sent_recently', true);
            return $this->redirectToRoute('app_login');
        }

        try {
            // Générer un nouveau token
            $token = bin2hex(random_bytes(32));
            $user->setRecupass($token);

            // Mettre à jour la date d'inscription
            $user->setDateInscription(new \DateTime());

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Capturer les variables nécessaires pour l'envoi d'email
            $userId = $user->getId();
            $userToken = $token;

            // Ajouter un écouteur d'événement pour envoyer l'email après la réponse
            $this->eventDispatcher->addListener(KernelEvents::TERMINATE, function () use ($userId, $userToken) {
                $user = $this->entityManager->getRepository(User::class)->find($userId);
                if ($user) {
                    $this->emailManagerService->sendValidationInscription($user, $userToken);
                }
            });

            // Rediriger immédiatement
            $session = $request->getSession();
            $session->set('from_email_sent', true);
            return $this->redirectToRoute('app_login');
        } catch (\Exception $e) {
            return $this->redirectToRoute('app_login', [
                'error' => urlencode('Une erreur est survenue lors de l\'envoi de l\'email. Veuillez réessayer plus tard.')
            ]);
        }
    }

    /**
     * @Route("/deconnexion", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
