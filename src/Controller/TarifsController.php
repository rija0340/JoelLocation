<?php

namespace App\Controller;

use App\Entity\Tarifs;
use App\Form\TarifsType;
use App\Repository\TarifsRepository;
use App\Repository\VehiculeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TarifsController extends AbstractController
{
    /**
     * @Route("/tarifs", name="tarifs_index")
     */
    public function index(TarifsRepository $tarifsRepo): Response
    {
        $listeTarifs = new Tarifs();
        $listeTarifs = $tarifsRepo->findAll();

        return $this->render('admin/tarifs/index.html.twig', [
            'controller_name' => 'TarifsController',
            'tarifs' => $listeTarifs
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
        $form = $this->createForm(TarifsType::class, $tarif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tarif_index');
        }

        return $this->render('admin/tarifs/edit.html.twig', [
            'tarif' => $tarif,
            'form' => $form->createView(),
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
        $mois = $request->query->get('mois');
        $vehicule = $vehiculeRepo->find($vehicule_id);
        $tarif =  $tarifsRepo->findTarifs($vehicule, $mois);

        $data = array();

        $data['troisJours'] = $tarif->getTroisJours();
        $data['septJours'] = $tarif->getSeptJours();
        $data['quinzeJours'] = $tarif->getQuinzeJours();
        $data['trenteJours'] = $tarif->getTrenteJours();


        return new JsonResponse($data);
    }
}
