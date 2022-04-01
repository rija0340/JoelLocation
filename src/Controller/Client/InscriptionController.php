<?php

namespace App\Controller\Client;

use App\Classe\Mailjet;
use App\Entity\User;
use App\Service\DateHelper;
use App\Form\ClientRegisterType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class InscriptionController extends AbstractController
{

    private $passwordEncoder;
    private $dateHelper;
    private $flashy;
    private $encoder;
    private $mail;

    public function __construct(

        UserPasswordEncoderInterface $passwordEncoder,
        DateHelper $dateHelper,
        FlashyNotifier $flashy,
        EncoderFactoryInterface $encoder,
        Mailjet $mail

    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->dateHelper = $dateHelper;
        $this->flashy = $flashy;
        $this->encoder = $encoder;
        $this->mail = $mail;
    }
    
    /**
     * @Route("/inscription", name="inscription", methods={"GET","POST"})
     */
    public function inscription(Request $request): Response
    {

        $user = new User();
        $form = $this->createForm(ClientRegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();
            $password = $this->passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setRoles(['ROLE_CLIENT']);

            $user->setPassword($password);
            //$user->setUsername($request->request->get('client_register')['nom']);
            $user->setUsername($user->getNom());
            $user->setRecupass($user->getPassword());
            $user->setPresence(1);
            $user->setDateInscription($this->dateHelper->dateNow());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $url = $this->generateUrl('app_login');

            $objet = "Confirmation d'inscription";
            $password = "";


            //envoi de mmail de confirmation de creation de compte au client 
            $this->mail->confirmationInscription($user->getNom(),$user->getMail(),$objet, $password);

            return $this->redirectToRoute('app_login');
        }
        return $this->render('accueil/inscription.html.twig', [
            'controller_name' => 'InscriptionController',
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
