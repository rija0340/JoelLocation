<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Form\DevisType;
use App\Repository\UserRepository;
use App\Repository\DevisRepository;
use App\Repository\VehiculeRepository;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DevisController extends AbstractController
{

    private $reservationRepo;
    private $userRepo;
    private $vehiculeRepo;
    private $devisRepo;

    public function __construct(DevisRepository $devisRepo, ReservationRepository $reservationRepo, VehiculeRepository $vehiculeRepo, UserRepository $userRepo)
    {

        $this->reservationRepo = $reservationRepo;
        $this->vehiculeRepo = $vehiculeRepo;
        $this->userRepo = $userRepo;
        $this->devisRepo = $devisRepo;
    }

    /**
     * @Route("/devis", name="devis_index")
     */
    public function index(): Response
    {
        $devis = $this->devisRepo->findAll();

        return $this->render('admin/devis/index.html.twig', [
            'controller_name' => 'DevisController',
            'devis' => $devis
        ]);
    }


    /**
     * @Route("/newDevis", name="devis_new", methods={"GET","POST"})
     */
    public function newDevis(Request $request): Response
    {

        if ($request->isXmlHttpRequest()) {

            $devis = new Devis();
            $idClient =  $request->query->get('idClient');
            $agenceDepart = $request->query->get('agenceDepart');
            $agenceRetour = $request->query->get('agenceRetour');
            $lieuSejour = $request->query->get('lieuSejour');
            $dateTimeDepart = $request->query->get('dateTimeDepart');
            $dateTimeRetour = $request->query->get('dateTimeRetour');
            $vehiculeIM = $request->query->get('vehiculeIM');
            $conducteur = $request->query->get('conducteur');
            $siege = $request->query->get('siege');
            $garantie = $request->query->get('garantie');

            $vehicule = $this->vehiculeRepo->findByIM($vehiculeIM);
            // $client = $this->userRepo->findByNameAndMail($nomClient, $emailClient);
            $client = $this->userRepo->find($idClient);

            $duree = date_diff(new \DateTime($dateTimeDepart), new \DateTime($dateTimeRetour));

            $devis->setVehicule($vehicule);
            $devis->setClient($client);
            $devis->setAgenceDepart($agenceDepart);
            $devis->setAgenceRetour($agenceRetour);
            $devis->setDateDepart(new \DateTime($dateTimeDepart));
            $devis->setDateRetour(new \DateTime($dateTimeRetour));
            $devis->setGarantie(['garantie' => $garantie]);
            $devis->setSiege(['siege' => $siege]);
            $devis->setConducteur($conducteur);
            $devis->setLieuSejour($lieuSejour);
            $devis->setDuree($duree->format('%d'));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($devis);
            $entityManager->flush();
        }

        $devis = $this->devisRepo->findAll();

        return $this->render('admin/devis/index.html.twig', [
            'controller_name' => 'DevisController',
            'devis' => $devis
        ]);
    }


    /**
     * @Route("devis/{id}", name="devis_show", methods={"GET"})
     */
    public function show(Devis $devis): Response
    {
        return $this->render('admin/devis/show.html.twig', [
            'devis' => $devis,
        ]);
    }


    /**
     * @Route("devis/{id}/edit", name="devis_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Devis $devis): Response
    {
        $form = $this->createForm(DevisType::class, $devis);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('devis_index');
        }

        return $this->render('admin/devis/edit.html.twig', [
            'form' => $form->createView(),
            'devis' => $devis
        ]);
    }

    /**
     * @Route("/{id}", name="devis_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Devis $devis): Response
    {
        if ($this->isCsrfTokenValid('delete' . $devis->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($devis);
            $entityManager->flush();
        }

        return $this->redirectToRoute('devis_index');
    }
}
