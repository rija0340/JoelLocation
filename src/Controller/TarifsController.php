<?php

namespace App\Controller;

use App\Entity\Tarifs;
use App\Form\TarifsType;
use App\Form\TarifEditType;
use App\Repository\MarqueRepository;
use App\Repository\ModeleRepository;
use App\Repository\TarifsRepository;
use App\Repository\VehiculeRepository;
use App\Service\DateHelper;
use App\Service\TarifsHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TarifsController extends AbstractController
{

    private $marqueRepo;
    private $modeleRepo;
    private $tarifsHelper;
    private $dateHelper;

    public function __construct(DateHelper $dateHelper, TarifsHelper $tarifsHelper, MarqueRepository $marqueRepo, ModeleRepository $modeleRepo)
    {
        $this->marqueRepo = $marqueRepo;
        $this->modeleRepo = $modeleRepo;
        $this->tarifsHelper = $tarifsHelper;
        $this->dateHelper = $dateHelper;
    }

    /**
     * @Route("/tarifs", name="tarifs_index")
     */
    public function index(TarifsRepository $tarifsRepo): Response
    {



        $listeTarifs = new Tarifs();
        $listeTarifs =  $tarifsRepo->findAll();
        $nbrTarifs = count($listeTarifs);
        $listeVehicule = [];
        $listeUniqueVehicules = [];

        $listeMois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];


        $marques = $this->marqueRepo->findAll();
        $modeles = $this->modeleRepo->findAll();
        foreach ($marques as $marque) {
            foreach ($modeles as $modele) {

                if ($marque == $modele->getMarque()) {
                    array_push($listeUniqueVehicules, $marque . " " . $modele);
                }
            }
        }


        $tarifsParVehicule = [];
        //arrangement des donnée Janvier -> décembre
        foreach ($listeUniqueVehicules as $veh) {
            dump($veh);
            $ordered = [];
            foreach ($listeMois as $mois) {
                $i = 0;
                foreach ($listeTarifs as $tarif) {
                    if ($tarif->getMois() == $mois && $tarif->getMarque()->getLibelle() . " " . $tarif->getModele()->getLibelle() == $veh) {
                        $i = $i + 1;
                        array_push($ordered, $tarif);
                    }
                }
                if ($i == 0) {
                    array_push($ordered, null);
                }
            }
            array_push($tarifsParVehicule, $ordered);
        }

        return $this->render('admin/tarifs/index.html.twig', [
            'controller_name' => 'TarifsController',
            'tarifs' => $listeTarifs,
            'listeMois' => $listeMois,
            'listeVehicules' => $listeUniqueVehicules,
            'tarifsParVehicule' => $tarifsParVehicule
        ]);
    }

    /**
     * @Route("/tarif/new", name="tarif_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {

        $tarif = new Tarifs();

        $form = $this->createForm(TarifsType::class, $tarif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $modeleId =  $request->request->get('selectModele');
            $mois =  $request->request->get('selectMois');
            $modele =  $this->modeleRepo->find($modeleId);
            $tarif->setModele($modele);
            $tarif->setMois($mois);

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($tarif);
            $entityManager->flush();

            return $this->redirectToRoute('tarifs_index');
        }

        return $this->render('admin/tarifs/new.html.twig', [
            'tarif' => $tarif,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/tarif/{id}", name="tarif_show", methods={"GET"})
     */
    public function show(Tarifs $tarif): Response
    {
        return $this->render('admin/tarifs/show.html.twig', [
            'tarif' => $tarif,
        ]);
    }

    /**
     * @Route("/tarif/{id}/edit", name="tarif_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Tarifs $tarif): Response
    {
        $formEdit = $this->createForm(TarifEditType::class, $tarif);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tarif_index');
        }

        return $this->render('admin/tarifs/edit.html.twig', [
            'tarif' => $tarif,
            'formEdit' => $formEdit->createView(),
        ]);
    }

    /**
     * @Route("/tarif/{id}", name="tarif_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Tarifs $tarif): Response
    {
        if ($this->isCsrfTokenValid('delete' . $tarif->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($tarif);
            $entityManager->flush();
        }
        return $this->redirectToRoute('tarifs_index');
    }

    /**
     * @Route("/tarifVenteComptoir", name="tarif_vente_comptoir", methods={"GET"})
     */
    public function tarifVenteComptoir(Request $request, VehiculeRepository $vehiculeRepo, TarifsRepository $tarifsRepo)
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
     * @Route("/listeMoisSansValeur", name="listeMoisSansValeur", methods={"GET"})
     * @return tableau contenant mois contenant tarifs
     */
    public function listeMoisSansValeur(Request $request, VehiculeRepository $vehiculeRepo, TarifsRepository $tarifsRepo)
    {
        $marqueID = intVal($request->query->get('marqueID'));
        $modeleID = intVal($request->query->get('modeleID'));

        $marque = $this->marqueRepo->find($marqueID);
        $modele = $this->modeleRepo->find($modeleID);

        $tarifs = $tarifsRepo->findBy(['marque' => $marque, 'modele' => $modele]);

        $listeMois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

        $ordered = [];
        foreach ($listeMois as $mois) {
            $i = 0;
            foreach ($tarifs as $tarif) {
                if ($tarif->getMois() == $mois) {
                    $i = $i + 1;
                    array_push($ordered, $tarif->getMois());
                }
            }
            if ($i == 0) {
                array_push($ordered, "");
            }
        }

        $moisSansVal = [];
        for ($i = 0; $i < 12; $i++) {
            if ($ordered[$i] == "") {
                array_push($moisSansVal, $listeMois[$i]);
            }
        }
        return new JsonResponse($moisSansVal);
    }
}
