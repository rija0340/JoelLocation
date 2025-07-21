<?php

namespace App\Controller;

use App\Classe\Mailjet;
use App\Service\DateHelper;
use App\Entity\AppelPaiement;
use App\Repository\PaiementRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use App\Repository\AppelPaiementRepository;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\SymfonyMailerHelper;
use App\Entity\Reservation;

class AppelPaiementController extends AbstractController
{

    private $paiementRepo;
    private $reservationRepo;
    private $appelPaiementRepo;
    private $em;
    private $mailjet;
    private $flashy;
    private $dateHelper;

    public function __construct(
        DateHelper $dateHelper,
        FlashyNotifier $flashy,
        Mailjet $mailjet,
        EntityManagerInterface $em,
        AppelPaiementRepository $appelPaiementRepo,
        PaiementRepository $paiementRepo,
        ReservationRepository $reservationRepo
    ) {
        $this->paiementRepo = $paiementRepo;
        $this->reservationRepo = $reservationRepo;
        $this->appelPaiementRepo = $appelPaiementRepo;
        $this->em = $em;
        $this->mailjet = $mailjet;
        $this->flashy = $flashy;
        $this->dateHelper = $dateHelper;
    }

    /**
     * @Route("backoffice/appel-paiement", name="appel_paiement_index")
     */
    public function index(): Response
    {
 
        //apres mise a jour de la bdd , retrait de toutes les données
        $appelPaiements = $this->appelPaiementRepo->findAll();

        return $this->render('admin/reservation/appel_paiement/index.html.twig', [
            'appelPaiements' => $appelPaiements
        ]);
    }
    
    /**
     * @Route("backoffice/envoi-email-appel-paiement/{id}", name="envoi_email_appel_paiement_index")
     */
    public function sendMailAppelPaiement(Request $request, AppelPaiement $appelPaiement, SymfonyMailerHelper $symfonyMailerHelper)
    {

        $symfonyMailerHelper->sendAppelPaiement(
            $appelPaiement->getReservation()
        );

        $this->flashy->success("Votre mail a été envoyé");

        // $appelPaiement->setDateDemande($this->dateHelper->dateNow());
        $appelPaiement->addSentDate(new \DateTimeImmutable());
        $this->em->flush();

        return $this->redirectToRoute('appel_paiement_index');
    }
}
