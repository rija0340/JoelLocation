<?php

namespace App\Controller;

use App\Entity\EtatReservation;
use App\Form\EtatReservationType;
use App\Repository\EtatReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/etat/reservation")
 */
class EtatReservationController extends AbstractController
{
    /**
     * @Route("/", name="etat_reservation_index", methods={"GET"})
     */
    public function index(EtatReservationRepository $etatReservationRepository): Response
    {
        return $this->render('etat_reservation/index.html.twig', [
            'etat_reservations' => $etatReservationRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="etat_reservation_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $etatReservation = new EtatReservation();
        $form = $this->createForm(EtatReservationType::class, $etatReservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($etatReservation);
            $entityManager->flush();

            return $this->redirectToRoute('etat_reservation_index');
        }

        return $this->render('etat_reservation/new.html.twig', [
            'etat_reservation' => $etatReservation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="etat_reservation_show", methods={"GET"})
     */
    public function show(EtatReservation $etatReservation): Response
    {
        return $this->render('etat_reservation/show.html.twig', [
            'etat_reservation' => $etatReservation,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="etat_reservation_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, EtatReservation $etatReservation): Response
    {
        $form = $this->createForm(EtatReservationType::class, $etatReservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('etat_reservation_index');
        }

        return $this->render('etat_reservation/edit.html.twig', [
            'etat_reservation' => $etatReservation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="etat_reservation_delete", methods={"DELETE"})
     */
    public function delete(Request $request, EtatReservation $etatReservation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$etatReservation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($etatReservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('etat_reservation_index');
    }
}
