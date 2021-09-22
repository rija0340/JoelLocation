<?php

namespace App\Controller\Client;

use App\Entity\User;
use App\Service\DateHelper;
use App\Form\ClientRegisterType;
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

    public function __construct(

        UserPasswordEncoderInterface $passwordEncoder,
        DateHelper $dateHelper,
        FlashyNotifier $flashy,
        EncoderFactoryInterface $encoder

    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->dateHelper = $dateHelper;
        $this->flashy = $flashy;
        $this->encoder = $encoder;
    }
    /**
     * @Route("/inscription", name="inscription")
     */
    public function inscription(Request $request, UserRepository $userrepo): Response
    {

        $user = new User();
        $form = $this->createForm(ClientRegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();
            $password = $this->passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setRoles(['ROLE_CLIENT']);
            $user->setPassword($password);
            $user->setRecupass($user->getPassword());
            $user->setPresence(1);
            $user->setDateInscription($this->dateHelper->dateNow());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->flashy->success('Votre compte a bien été enregistrer, Veuillez vous connecter');

            return $this->redirectToRoute('app_login');
        }
        return $this->render('accueil/inscription2.html.twig', [
            'controller_name' => 'InscriptionController',
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
