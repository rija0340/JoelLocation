<?php

namespace App\Controller\Client;

use App\Entity\Avis;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Repository\AvisRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AvisController extends AbstractController
{
    private $avisRepo;
    private $reservationRepo;
    private $em;
    private $flashy;
    private $dateHelper;

    public function __construct(
        DateHelper $dateHelper,
        FlashyNotifier $flashy,
        AvisRepository $avisRepo,
        ReservationRepository $reservationRepo,
        EntityManagerInterface $em
    ) {
        $this->avisRepo = $avisRepo;
        $this->reservationRepo = $reservationRepo;
        $this->em = $em;
        $this->flashy = $flashy;
        $this->dateHelper = $dateHelper;
    }


    /**
     * @Route("/espaceclient/ajouter-avis/{id}", name="add_avis", methods={"GET", "POST"})
     */
    public function addAvis(Request $request, Reservation $reservation): Response
    {

        return $this->render('client/avis/index.html.twig', [
            'reservation' => $reservation
        ]);
    }

    /**
     * @Route("/espaceclient/enregistrer-avis", name="save_avis", methods={"GET", "POST"})
     */
    public function saveAvis(Request $request): Response
    {

        $global = $request->request->get('global');
        $ponctualite = $request->request->get('ponctualite');
        $accueil = $request->request->get('accueil');
        $service = $request->request->get('service');
        $commentaire = $request->request->get('commentaire');

        $idReservation = $request->request->get('idReservation');

        $avis = new Avis();

        $avis->setGlobal($global);
        $avis->setPonctualite($ponctualite);
        $avis->setAccueil($accueil);
        $avis->setService($service);
        $avis->setCommentaire($commentaire);
        $avis->setReservation($this->reservationRepo->find($idReservation));
        $avis->setDate($this->dateHelper->dateNow());

        $this->em->persist($avis);
        $this->em->flush();

        $this->flashy->success('Votre avis a été ajouté avec succès');

        return $this->redirectToRoute('espaceClient_index');
    }
}
