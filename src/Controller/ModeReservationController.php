<?php

namespace App\Controller;

use App\Entity\ModeReservation;
use App\Form\ModeReservationType;
use App\Repository\ModeReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/mode/reservation")
 */
class ModeReservationController extends AbstractController
{
    /**
     * @Route("/", name="mode_reservation_index", methods={"GET"})
     */
    public function index(ModeReservationRepository $modeReservationRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $modeReservationRepository->findBy([], ["id" => "DESC"]), /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );
        return $this->render('mode_reservation/index.html.twig', [
            'mode_reservations' => $pagination,
        ]);
    }

    /**
     * @Route("/new", name="mode_reservation_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $modeReservation = new ModeReservation();
        $form = $this->createForm(ModeReservationType::class, $modeReservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($modeReservation);
            $entityManager->flush();

            return $this->redirectToRoute('mode_reservation_index');
        }

        return $this->render('mode_reservation/new.html.twig', [
            'mode_reservation' => $modeReservation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="mode_reservation_show", methods={"GET"})
     */
    public function show(ModeReservation $modeReservation): Response
    {
        return $this->render('mode_reservation/show.html.twig', [
            'mode_reservation' => $modeReservation,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="mode_reservation_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ModeReservation $modeReservation): Response
    {
        $form = $this->createForm(ModeReservationType::class, $modeReservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('mode_reservation_index');
        }

        return $this->render('mode_reservation/edit.html.twig', [
            'mode_reservation' => $modeReservation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="mode_reservation_delete", methods={"DELETE"})
     */
    public function delete(Request $request, ModeReservation $modeReservation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$modeReservation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($modeReservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('mode_reservation_index');
    }
}
