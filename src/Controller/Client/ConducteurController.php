<?php

namespace App\Controller\Client;

use App\Entity\Conducteur;
use App\Form\ConducteurType;
use App\Repository\ConducteurRepository;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConducteurController extends AbstractController
{


    private $conducteurRepo;
    private $flashy;


    public function __construct(

        ConducteurRepository $conducteurRepo,
        FlashyNotifier $flashy

    ) {
        $this->conducteurRepo = $conducteurRepo;
        $this->flashy = $flashy;
    }


    /** 
     * @Route("/espaceclient/mes-conducteurs", name="client_mesConducteurs", methods={"GET","POST"})
     */
    public function index(Request $request): Response
    {
        $client = $this->getUser();
        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }
        $conducteurs = $this->conducteurRepo->findBy(['client' => $client]);

        // $formClient = $this->createForm(ClientType::class, $client);

        return $this->render('client/conducteur/index.html.twig', [

            'client' => $client,
            'conducteurs' => $conducteurs
        ]);
    }

    /** 
     * @Route("/espaceclient/conducteur/nouveau/", name="client_newConducteur", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {

        $conducteur = new Conducteur();
        $client = $this->getUser();
        if ($client == null) {
            return $this->redirectToRoute('app_login');
        }
        $formConducteur = $this->createForm(ConducteurType::class, $conducteur);
        $formConducteur->handleRequest($request);

        if ($formConducteur->isSubmitted() && $formConducteur->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $conducteur->setClient($client);
            $entityManager->persist($conducteur);
            $entityManager->flush();

            $this->flashy->success('Votre conducteur a bien été enregistré');
            return $this->redirectToRoute('client_mesConducteurs');
        }

        return $this->render('client/conducteur/new.html.twig', [

            'formConducteur' => $formConducteur->createView()

        ]);
    }


    /**
     * @Route("/espaceclient/conducteur/modifier/{id}", name="conducteur_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Conducteur $conducteur): Response
    {
        $formConducteur = $this->createForm(ConducteurType::class, $conducteur);
        $formConducteur->handleRequest($request);

        if ($formConducteur->isSubmitted() && $formConducteur->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            $this->flashy->success('Votre conducteur a bien été modifié');
            return $this->redirectToRoute('client_mesConducteurs');
        }

        return $this->render('client/conducteur/edit.html.twig', [
            'formConducteur' => $formConducteur->createView(),
        ]);
    }
}
