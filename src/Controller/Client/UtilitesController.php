<?php

namespace App\Controller\Client;

use App\Entity\Devis;
use App\Entity\Vehicule;
use App\Repository\GarantieRepository;
use App\Repository\OptionsRepository;
use App\Repository\TarifsRepository;
use App\Repository\VehiculeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UtilitesController extends AbstractController
{


    private $optionsRepo;
    private $garantieRepo;


    public function __construct(
        OptionsRepository $optionsRepo,
        GarantieRepository $garantieRepo

    ) {
        $this->optionsRepo = $optionsRepo;
        $this->garantieRepo = $garantieRepo;
    }


    /**
     * @Route("/espaceclient/detailsVehicule", name="client_detailsVehicule", methods={"GET"})
     */
    public function detailsVehicule(VehiculeRepository $vehiculeRepository, Request $request)
    {
        $vehicule = new Vehicule;
        $id = intVal($request->query->get('vehicule_id'));
        $vehicule =  $vehiculeRepository->find($id);

        $data = array();

        $data['id'] = $vehicule->getId();
        $data['marque'] = $vehicule->getMarque()->getLibelle();
        $data['modele'] = $vehicule->getModele()->getLibelle();
        $data['carburation'] = $vehicule->getCarburation();
        $data['vitesse'] = $vehicule->getVitesse();
        $data['immatriculation'] = $vehicule->getImmatriculation();
        $data['bagages'] = $vehicule->getBagages();
        $data['atouts'] = $vehicule->getAtouts();
        $data['caution'] = $vehicule->getCaution();
        $data['details'] = $vehicule->getDetails();
        $data['portes'] = $vehicule->getPortes();
        $data['passagers'] = $vehicule->getPassagers();
        $data['image'] = $vehicule->getImage();

        return new JsonResponse($data);
    }

    /**
     * @Route("/espaceclient/tarifsVehicule", name="client_tarifsVehicule", methods={"GET"})
     */
    public function tarifsVehicule(Request $request, VehiculeRepository $vehiculeRepo, TarifsRepository $tarifsRepo)
    {
        $vehicule_id = intVal($request->query->get('vehicule_id'));
        $dateDepart = $request->query->get('dateDepart');
        $dateRetour = $request->query->get('dateRetour');

        $dateDepart = $this->dateHelper->newDate($dateDepart);
        $dateRetour = $this->dateHelper->newDate($dateRetour);

        $vehicule = $vehiculeRepo->find($vehicule_id);
        $tarif = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);

        $data = array();

        if ($tarif != null) {

            $data['tarif'] = $tarif;
        } else {
            $data['tarif'] = 0;
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/espaceclient/listeOptions", name="client_listeOptions", methods={"GET"})
     */
    public function client_listeOptions(Request $request)
    {
        $data = array();
        $options = $this->optionsRepo->findAll();

        foreach ($options as $key => $option) {

            $data[$key]['id'] = $option->getId();
            $data[$key]['appelation'] = $option->getAppelation();
            $data[$key]['prix'] = $option->getPrix();
        }

        return new JsonResponse($data);
    }


    /**
     * @Route("/espaceclient/listeGaranties", name="client_listeGaranties", methods={"GET"})
     */
    public function client_listeGaranties(Request $request)
    {
        $data = array();
        $garanties = $this->garantiesRepo->findAll();

        foreach ($garanties as $key => $garantie) {

            $data[$key]['id'] = $garantie->getId();
            $data[$key]['appelation'] = $garantie->getAppelation();
            $data[$key]['prix'] = $garantie->getPrix();
        }

        return new JsonResponse($data);
    }


    /**
     * @Route("/espaceclient/devisPDF/{id}", name="devisPDF", methods={"GET","POST"})
     */
    public function devisPDF(Request $request, Devis $devis)
    {

        $data = array();

        $data['numeroDevisValue'] = $devis->getNumero();
        $data['dateDepartValue'] = $devis->getDateDepart()->format('d/m/Y H:i');
        $data['dateRetourValue'] = $devis->getDateRetour()->format('d/m/Y H:i');
        $data['nomClientValue'] = $devis->getClient()->getNom();
        $data['prenomClientValue'] = $devis->getClient()->getPrenom();
        $data['vehiculeValue'] = $devis->getVehicule()->getMarque()->getLibelle() . " " . $devis->getVehicule()->getModele()->getLibelle() . " " . $devis->getVehicule()->getImmatriculation();
        $data['dureeValue'] = $devis->getDuree();
        $data['agenceDepartValue'] = $devis->getAgenceDepart();
        $data['agenceRetourValue'] = $devis->getAgenceRetour();
        $data['tarifValue'] = $devis->getPrix();
        $data['adresseClientValue'] = $devis->getClient()->getAdresse();

        return new JsonResponse($data);
    }
}
