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
        //creer un entité appel paiement pour chaque reservation
        $appelPaiements1 = $this->appelPaiementRepo->findAll();

        $reservationsInvolved = [];
        foreach ($appelPaiements1 as $appel) {
            array_push($reservationsInvolved, $appel->getReservation());
        }

        foreach ($reservations as $reservation) {

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
        $nom_client = $appelPaiement->getReservation()->getClient()->getNom();
        $reference_reservation = $appelPaiement->getReservation()->getReference();
        $montant = $appelPaiement->getMontant();
        $subject = "Appel à paiement";
        $message = "Bonjour " . $nom_client . ", nous vous envoyons cet email pour vous rappeler que vous n'avez pas encore regularisé le paiement de votre réservation N° " . $reference_reservation . ". Ceci est un montant de " . $montant . " €";

        $this->mailjet->send($email_to, $nom_client, $subject, $message);
        $this->flashy->success("Votre mail a été envoyé");

        $appelPaiement->setDateDemande($this->dateHelper->dateNow());
        $this->em->flush();

        return $this->redirectToRoute('appel_paiement_index');
    }
}
