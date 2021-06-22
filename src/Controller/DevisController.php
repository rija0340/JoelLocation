<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Form\DevisType;
use App\Repository\UserRepository;
use App\Repository\DevisRepository;
use App\Repository\GarantieRepository;
use App\Repository\OptionsRepository;
use App\Repository\VehiculeRepository;
use App\Repository\ReservationRepository;
use App\Repository\TarifsRepository;
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
    private $tarifsRepo;
    private $garantiesRepo;
    private $optionsRepo;

    public function __construct(DevisRepository $devisRepo, ReservationRepository $reservationRepo, VehiculeRepository $vehiculeRepo, UserRepository $userRepo, TarifsRepository $tarifsRepo, OptionsRepository $optionsRepo, GarantieRepository $garantiesRepo)
    {

        $this->reservationRepo = $reservationRepo;
        $this->vehiculeRepo = $vehiculeRepo;
        $this->userRepo = $userRepo;
        $this->devisRepo = $devisRepo;
        $this->tarifsRepo = $tarifsRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->optionsRepo = $optionsRepo;
    }

    /**
     * @Route("/devis", name="devis_index")
     */
    public function index(): Response
    {
        $devis = $this->devisRepo->findAll();

        return $this->render('admin/devis/index.html.twig', [
            'devis' => $devis
        ]);
    }


    /**
     * @Route("/newDevis", name="devis_new", methods={"GET","POST"})
     */
    public function newDevis(Request $request): Response
    {
        $devis = new Devis();
        if ($request->isXmlHttpRequest()) {
            $idClient =  $request->query->get('idClient');
            $agenceDepart = $request->query->get('agenceDepart');
            $agenceRetour = $request->query->get('agenceRetour');
            $lieuSejour = $request->query->get('lieuSejour');
            $dateTimeDepart = $request->query->get('dateTimeDepart');
            $dateTimeRetour = $request->query->get('dateTimeRetour');
            $vehiculeIM = $request->query->get('vehiculeIM');
            $conducteur = $request->query->get('conducteur');
            $idSiege = $request->query->get('idSiege');
            $idGarantie = $request->query->get('idGarantie');


            $siege = $this->optionsRepo->find($idSiege);
            $garantie = $this->garantiesRepo->find($idGarantie);

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
            $devis->setGarantie($garantie);
            $devis->setSiege($siege);
            $devis->setConducteur($conducteur);
            $devis->setLieuSejour($lieuSejour);
            $devis->setDuree($duree->format('%d'));
            $devis->setDateCreation(new \DateTime('NOW'));

            //recuperation tarif en fonction mois départ et véhicule
            $mois = $this->monthName($devis->getDateDepart()->format('m'));
            $tarifs = $this->tarifsRepo->findTarifs($vehicule, $mois);

            if ($duree->format('%d') <= 3) $tarif = $tarifs->getTroisJours();

            if ($duree->format('%d') > 3 && $duree->format('%d') <= 7) $tarif = $tarifs->getSeptJours();

            if ($duree->format('%d') > 7 && $duree->format('%d') <= 15) $tarif = $tarifs->getQuinzeJours();

            if ($duree->format('%d') > 15 && $duree->format('%d') <= 30) $tarif = $tarifs->getTrenteJours();

            $devis->setPrix($tarif + $garantie->getPrix() + $siege->getPrix());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($devis);
            $entityManager->flush();

            return $this->redirectToRoute('devis_index');
        }

        $devis = $this->devisRepo->findAll();

        return $this->render('admin/devis/index.html.twig', [
            'devis' => $devis
        ]);
    }


    /**
     * @Route("devis/{id}", name="devis_show", methods={"GET"})
     */
    public function show(Devis $devis): Response
    {

        return $this->render('admin/devis/details.html.twig', [
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

    function monthName($month)
    {
        $monthFR = null;
        switch ($month) {
            case "01":
                $monthFR = 'Janvier';
                break;
            case "02":
                $monthFR = 'Février';
                break;
            case "03":
                $monthFR = 'Mars';
                break;
            case "04":
                $monthFR = 'Avril';
                break;
            case "05":
                $monthFR = 'Mai';
                break;
            case "06":
                $monthFR = 'Juin';
                break;
            case "07":
                $monthFR = 'Juillet';
                break;
            case "08":
                $monthFR = 'Août';
                break;
            case "09":
                $monthFR = 'Septembre';
                break;
            case "10":
                $monthFR = 'Octobre';
                break;
            case "11":
                $monthFR = 'Novembre';
                break;
            case "12":
                $monthFR = 'Décembre';
                break;
        }

        return $monthFR;
    }
}
