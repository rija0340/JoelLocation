<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @Route("/teste", name="teste")
     */
    public function teste(): Response
    {
        return $this->render('accueil/teste.html.twig', [
            'controller_name' => 'TesteController',
        ]);
    }
}
