<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Classe\Mailjet;
use App\Entity\ResetPassword;
use App\Form\ResetPasswordType;
use App\Repository\ResetPasswordRepository;
use App\Repository\UserRepository;
use App\Service\DateHelper;
use App\Service\Site;
use Doctrine\ORM\EntityManagerInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

class ResetPasswordController extends AbstractController
{

    private $entityManager;
    private $userRepo;
    private $dateHelper;
    private $resetPasswordRepo;
    private $flashy;
    private $passwordEncoder;
    private $site;
    private $mailjet;


    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepo,
        DateHelper $dateHelper,
        ResetPasswordRepository $resetPasswordRepo,
        FlashyNotifier $flashy,
        UserPasswordEncoderInterface $passwordEncoder,
        Site $site,
        Mailjet $mailjet

    ) {

        $this->entityManager = $entityManager;
        $this->userRepo = $userRepo;
        $this->dateHelper = $dateHelper;
        $this->resetPasswordRepo = $resetPasswordRepo;
        $this->flashy = $flashy;
        $this->passwordEncoder = $passwordEncoder;
        $this->site = $site;
        $this->mailjet = $mailjet;
    }



    /**
     * @Route("/mot-de-passe-oublie", name="reset_password")
     */
    public function index(Request $request): Response
    {

        // s'il y a un user connecté rediriger vers home
        if ($this->getUser()) {
            $this->redirectToRoute('accueil');
        }

        if ($request->get('email')) {
            $user = $this->userRepo->findOneByEmail($request->get('email'));

            if ($user) {
                //enregistrer en base la demande de resetpassowrd
                $resetpassword = new ResetPassword();

                $resetpassword->setUser($user);
                $resetpassword->setToken(uniqid());
                $resetpassword->setCreatedAt($this->dateHelper->dateNow());
                $this->entityManager->persist($resetpassword);
                $this->entityManager->flush();

                //envoyer un email a l'utilisateur avec nouveau mot de passe 

                $url = $this->generateUrl('update_password', [

                    'token' => $resetpassword->getToken()
                ]);

                $baseUrl  = $this->site->getBaseUrl($request) . $url;

                $this->mailjet->resetPassword($user->getNom(), $user->getPrenom(), $user->getMail(), "Réinitialiser votre mot de passe", $baseUrl);

                $this->flashy->success("Vous allez recevoir un email avec la procédure pour réinitialiser votre mot de passe");
            } else {

                $this->flashy->error("Cette adresse email est inconnue.");
            }
        }
        return $this->render('reset_password/index.html.twig');
    }


    /**
     * @Route("/modifier-mon-mot-de-passe-oublie/{token}", name="update_password")
     */
    public function update($token, Request $request)
    {

        $resetpassword = $this->resetPasswordRepo->findOneByToken($token);

        if (!$resetpassword) {
            return $this->redirectToRoute('reset_password');
        }

        //verifier si createdAt = now - 3h

        $now = $this->dateHelper->dateNow();

        if ($now >  $resetpassword->getCreatedAt()->modify('+ 3 hour')) {
            $this->flashy->error("Votre demande de mot de passe a expiré. Merci de la renouveler");
            return $this->redirectToRoute('reset_password');
        }

        //rndre une vue avec mot de passe et confirmez 
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $new_password = $request->request->get('reset_password')['new_password']['first'];

            //encodage mot de passe 
            $password = $this->passwordEncoder->encodePassword($resetpassword->getUser(), $new_password);

            $resetpassword->getUser()->setPassword($password);
            //flush dd 
            $this->entityManager->flush();

            $this->flashy->success("Votre mot de passe a bien été mise à jour");
            $this->addFlash('message', "Votre mot de passe a bien été mise à jour");
            return $this->redirectToRoute('app_login');
        }
        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
