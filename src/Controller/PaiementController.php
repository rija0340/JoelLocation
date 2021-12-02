<?php

namespace App\Controller;

use App\Entity\Paiement;
use App\Form\PaiementType;
use App\Form\CalculPaiementsType;
use App\Repository\PaiementRepository;
use App\Repository\ReservationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/paiement")
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
     * @Route("/chiffre-affaire-paiement", name="all_paiements", methods={"GET","POST"},requirements={"id":"\d+"})
     */
    public function chiffreAffaire(Request $request)
    {

        $dateDebut = new \DateTime($request->query->get('dateDebut'));
        $dateFin = new \DateTime($request->query->get('dateFin'));

        $somme = 0;

        $paiements = $this->paiementRepo->findByDates($dateDebut, $dateFin);
        foreach ($paiements as $paiement) {

            $somme = $somme + $paiement->getMontant();
        }
        // dd($paiements, $dateDebut, $dateFin);

        // foreach ($paiements as $paiement) {
        // $data = array();

        //     $data['numeroDevisValue'] = $paiement->getMontant();
        //     $data['dateDepartValue'] = $paiement->getDateDepart()->format('d/m/Y H:i');
        // }

        return new JsonResponse($somme);
    }
    /**
     * @Route("/", name="paiement_index", methods={"GET", "POST"})
     */
    public function index(PaiementRepository $paiementRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $reservations = $this->reservRepo->findReservationsSansStopSales();
        $paiements = $paiementRepository->findAll(["id" => "DESC"]);

        $form = $this->createForm(CalculPaiementsType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $dateDebut = $form->getData()['dateDebut'];
            $dateFin = $form->getData()['dateFin'];

            $paiments = $this->paiementRepo->findByDates($dateDebut, $dateFin);

            $somme = 0;
            foreach ($paiments as $paiment) {
                $somme = $somme  + $paiment->getMontant();
                return $this->render('admin/paiement/index.html.twig', [
                    'paiements' => $paiements,
                    'reservations' => $reservations,
                    'form' => $form->createView(),
                    'somme' => $somme
                ]);
            }
        }
        return $this->render('admin/paiement/index.html.twig', [
            'paiements' => $paiements,
            'reservations' => $reservations,
            'form' => $form->createView(),
            'somme' => null

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
