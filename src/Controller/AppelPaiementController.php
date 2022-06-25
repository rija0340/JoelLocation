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
        $reservations = $this->reservationRepo->findAppelPaiement();
        //on met dans un tableau toutes les entities appelapaiement(chacun relié à une réservation)
        $appelPaiements1 = $this->appelPaiementRepo->findAll();

        //on met toutes les réservations reliés aux entités appels à paiement dans un tableau
        $reservationsInvolved = [];
        foreach ($appelPaiements1 as $appel) {
            array_push($reservationsInvolved, $appel->getReservation());
        }

        //comparer toutes les reservations existantes à celles qui sont déjà 
        foreach ($reservations as $reservation) {
            //si une réservation n'est pas encore dans l'encore la table appel alors qu'elle devra y être
            //on construit une nouvelle entité et on l'y insère 
            if (!in_array($reservation, $reservationsInvolved)) {

                $appelPaiement = new AppelPaiement();

                $appelPaiement->setReservation($reservation);
                $appelPaiement->setMontant($reservation->getPrix() - $reservation->getSommePaiements());
                $appelPaiement->setPayed(false);
                $this->em->persist($appelPaiement);
                $this->em->flush();
            }
        }

        //apres mise a jour de la bdd , retrait de toutes les données
        $appelPaiements2 = $this->appelPaiementRepo->findAll();

        return $this->render('admin/reservation/appel_paiement/index.html.twig', [
            'appelPaiements' => $appelPaiements2
        ]);
    }

    /**
     * @Route("backoffice/envoi-email-appel-paiement/{id}", name="envoi_email_appel_paiement_index")
     */
    public function sendMailAppelPaiement(Request $request, AppelPaiement $appelPaiement)
    {

        $email_to = $appelPaiement->getReservation()->getClient()->getMail();
        $reservation = $appelPaiement->getReservation();
        $nom_client = $appelPaiement->getReservation()->getClient()->getNom();
        $montant = $appelPaiement->getMontant();

        $this->mailjet->appelPaimentSolde(
            $nom_client,
            $email_to,
            "Appel à paiement",
            $reservation->getDateReservation()->format('d/m/Y H:i'),
            $reservation->getReference(),
            $reservation->getVehicule()->getMarque() . " " . $reservation->getVehicule()->getModele(),
            $reservation->getDateDebut()->format('d/m/Y H:i'),
            $reservation->getDateFin()->format('d/m/Y H:i'),
            $reservation->getPrix(),
            $reservation->getSommePaiements(),
            $montant
        );
        $this->flashy->success("Votre mail a été envoyé");

        $appelPaiement->setDateDemande($this->dateHelper->dateNow());
        $this->em->flush();

        return $this->redirectToRoute('appel_paiement_index');
    }
}
