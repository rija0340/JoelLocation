<?php

namespace App\Controller;

use App\Entity\Paiement;
use App\Form\PaiementType;
use App\Repository\PaiementRepository;
use App\Repository\ReservationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/backoffice/paiement")
 */
class PaiementController extends AbstractController
{
    private $reservRepo;
    private $paiementRepo;
    public function __construct(PaiementRepository $paiementRepo, ReservationRepository $reservRepo)
    {
        $this->reservRepo = $reservRepo;
        $this->paiementRepo = $paiementRepo;
    }
    /**
     * @Route("/liste-paiements", name="liste_paiements", methods={"GET","POST"},requirements={"id":"\d+"})
     */
    public function paiementsBydates(Request $request)
    {

        $dateDebut = new \DateTime($request->query->get('dateDebut'));
        $dateFin = new \DateTime($request->query->get('dateFin'));

        $paiements = $this->paiementRepo->findByDates($dateDebut, $dateFin);

        // dd($paiements, $dateDebut, $dateFin);

        $data = array();
        foreach ($paiements as $key => $paiement) {

            $data[$key]['reservation'] = $paiement->getReservation()->getReference();
            $data[$key]['montant'] = $paiement->getMontant();
            $data[$key]['client'] = $paiement->getReservation()->getClient()->getNom();
            $data[$key]['type'] = $paiement->getModePaiement()->getLibelle();
            $data[$key]['date'] = $paiement->getCreatedAt()->format('d/m/Y H:i');
            $data[$key]['reservationID'] = $paiement->getReservation()->getId();
        }

        return new JsonResponse($data);
    }
    /**
     * @Route("/", name="paiement_index", methods={"GET", "POST"})
     */
    public function index(PaiementRepository $paiementRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $reservations = $this->reservRepo->findReservationsSansStopSales();
        $paiements = $paiementRepository->findAll(["id" => "DESC"]);


        return $this->render('admin/paiement/index.html.twig', [
            'paiements' => $paiements,
            'reservations' => $reservations,
        ]);
    }

    /**
     * @Route("/new", name="paiement_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $paiement = new Paiement();
        $form = $this->createForm(PaiementType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($paiement);
            $entityManager->flush();

            return $this->redirectToRoute('paiement_index');
        }

        return $this->render('admin/paiement/new.html.twig', [
            'paiement' => $paiement,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="paiement_show", methods={"GET"})
     */
    public function show(Paiement $paiement): Response
    {
        return $this->render('admin/paiement/show.html.twig', [
            'paiement' => $paiement,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="paiement_edit", methods={"GET","POST"},requirements={"id":"\d+"})
     */
    public function edit(Request $request, Paiement $paiement): Response
    {
        $form = $this->createForm(PaiementType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('paiement_index');
        }

        return $this->render('admin/paiement/edit.html.twig', [
            'paiement' => $paiement,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="paiement_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Paiement $paiement): Response
    {
        if ($this->isCsrfTokenValid('delete' . $paiement->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($paiement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('paiement_index');
    }
}
