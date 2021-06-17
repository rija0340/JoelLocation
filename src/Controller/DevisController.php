<?php

namespace App\Controller;

use App\Repository\VehiculeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DevisController extends AbstractController
{
    /**
     * @Route("/devis", name="devis")
     */
    public function index(): Response
    {
        return $this->render('devis/index.html.twig', [
            'controller_name' => 'DevisController',
        ]);
    }


    /**
     * @Route("/new", name="devis_new", methods={"GET","POST"})
     */
    public function newVenteComtpoir(Request $request, VehiculeRepository $vehiculeRepository): Response
    {

        $client =  $request->query->get('client');
        $agenceDepart = $request->query->get('agenceDepart');
        $agenceRetour = $request->query->get('agenceRetour');
        $lieuSejour = $request->query->get('lieuSejour');
        $dateTimeDepart = $request->query->get('dateTimeDepart');
        $dateTimeRetour = $request->query->get('dateTimeRetour');
        $vehiculeIM = $request->query->get('vehiculeIM');
        $conducteur = $request->query->get('conducteur');
        $siege = $request->query->get('siege');
        $garantie = $request->query->get('garantie');


        dump($client, $agenceDepart, $agenceRetour,  $lieuSejour, $dateTimeDepart);
        die();
    }
}
