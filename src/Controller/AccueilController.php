<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ClientType;
use App\Form\LoginType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    /**
     * @Route("/", name="accueil")
     */
    public function index(): Response
    {
        return $this->render('accueil/index.html.twig', [
            'controller_name' => 'AccueilController',
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
     * @Route("/inscription", name="inscription")
     */
    public function inscription(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(ClientType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('accueil');
        }
        return $this->render('accueil/inscription.html.twig', [
            'controller_name' => 'InscriptionController',
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    
    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(LoginType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            //$entityManager->persist($user);
            //$entityManager->flush();

            return $this->redirectToRoute('accueil');
        }
        return $this->render('accueil/login.html.twig', [
            'controller_name' => 'LoginController',
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    
    /**
     * @Route("/teste", name="teste")
     */
    public function teste(): Response
    {
        return $this->render('accueil/teste.html.twig', [
            'controller_name' => 'TesteController',
        ]);
    }
}
