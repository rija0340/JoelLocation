<?php

namespace App\Controller\Client;

use App\Entity\User;
use App\Service\DateHelper;
use App\Form\ClientRegisterType;
use App\Repository\UserRepository;
use App\Service\EmailManagerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InscriptionController extends AbstractController
{

    private $passwordEncoder;
    private $dateHelper;
    private $userRepo;
    private $emailManagerService;
    private $logger;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        DateHelper $dateHelper,
        UserRepository $userRepo,
        EmailManagerService $emailManagerService,
        LoggerInterface $logger
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->dateHelper = $dateHelper;
        $this->userRepo = $userRepo;
        $this->emailManagerService = $emailManagerService;
        $this->logger = $logger;
    }

    /**
     * @Route("/inscription", name="inscription", methods={"GET","POST"})
     */
    public function inscription(Request $request): Response
    {
        $user = new User();

        // Pré-remplir avec des données de test
        if ($request->query->get('test') === '1') {
            $user->setNom('Dupont');
            $user->setPrenom('Jean');
            $user->setAdresse('123 Rue Test');
            $user->setSexe('masculin');
            $user->setTelephone('0123456789');
            $user->setPortable('0612345678');
            $user->setDateNaissance(new \DateTime('1990-01-01'));
            $user->setLieuNaissance('Paris');
            $user->setComplementAdresse('Apt 42');
            $user->setVille('Paris');
            $user->setCodePostal('75001');
            $user->setNumeroPermis('12345678');
            $user->setDatePermis(new \DateTime('2010-01-01'));
            $user->setVilleDelivrancePermis('Paris');
            // Ne pas pré-remplir l'email et le mot de passe
        }

        $form = $this->createForm(ClientRegisterType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer d'abord les données du formulaire
            $user = $form->getData();

            // Encoder le mot de passe
            $password = $this->passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            // Générer et définir le token APRÈS avoir récupéré les données du formulaire
            $token = bin2hex(random_bytes(32));
            $user->setRecupass($token);

            // Définir les autres propriétés
            $user->setRoles(['ROLE_CLIENT']);
            $user->setUsername($user->getNom());
            $user->setPresence(0);
            $user->setDateInscription($this->dateHelper->dateNow());

            // Persister l'utilisateur
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            try {
                // Envoyer l'email de validation
                $this->emailManagerService->sendValidationEmail(
                    $user->getMail(),
                    $user->getNom(),
                    $token
                );

                $session = $request->getSession();
                $session->set('from_inscription_session', true);
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                // Log l'erreur
                $this->logger->error('Erreur lors de l\'envoi de l\'email de validation: ' . $e->getMessage());

                // Rediriger avec un message d'erreur
                // return $this->redirectToRoute('app_login', [
                //     'error' => urlencode('Une erreur est survenue lors de l\'envoi de l\'email de validation. Veuillez contacter le support.')
                // ]);
                return $this->render('accueil/inscription.html.twig', [
                    'user' => $user,
                    'form' => $form->createView(),
                ]);
            }
        }

        return $this->render('accueil/inscription.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/validation-email/{token}", name="validate_email")
     */
    public function validateEmail(string $token, Request $request): Response
    {
        $user = $this->userRepo->findOneBy(['recupass' => $token]);

        if (!$user) {
            $this->logger->error('Token invalide ou non trouvé: ' . $token);

            $session = $request->getSession();
            $session->set('token_invalid', true);

            return $this->redirectToRoute('app_login');
        }

        // Vérifier si le token n'est pas expiré (par exemple, valide pendant 24h)
        $tokenCreationTime = $user->getDateInscription();
        $now = new \DateTime();
        $interval = $now->diff($tokenCreationTime);

        // Si le token a plus de 24 heures
        if ($interval->days >= 1) {
            $this->logger->error('Token expiré pour l\'utilisateur: ' . $user->getMail());

            // Stocker l'email en session pour pré-remplir le formulaire de login
            $session = $request->getSession();
            $session->set('client_email', $user->getMail());
            $session->set('token_expired', true);


            return $this->redirectToRoute('app_login');
        }

        // Activer le compte
        $user->setPresence(true);
        $user->setRecupass(null); // Effacer le token après utilisation

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        // Rediriger vers la page de connexion avec un paramètre indiquant que le compte vient d'être validé
        $session = $request->getSession();
        $session->set('client_email', $user->getMail());
        $session->set('form_validated_account', true);
        return $this->redirectToRoute('app_login');
    }
}
