<?php

namespace App\Classe;

use App\Entity\Devis;
use App\Entity\DevisOption;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Service\TarifsHelper;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ModeReservationRepository;
use App\Repository\ReservationRepository;
use App\Service\ReservationHelper;
use App\Entity\AppelPaiement;

class ReserverDevis
{
    private $em;
    private $tarifsHelper;
    private $dateHelper;
    private $modeReservationRepo;
    private $reservRepo;
    private $reservationHelper;
    private $reservationSession;

    public function __construct(
        EntityManagerInterface $em,
        TarifsHelper $tarifsHelper,
        DateHelper $dateHelper,
        ModeReservationRepository $modeReservationRepo,
        ReservationRepository $reservRepo,
        ReservationHelper $reservationHelper,
        ReservationSession $reservationSession
    ) {
        $this->em = $em;
        $this->tarifsHelper = $tarifsHelper;
        $this->dateHelper = $dateHelper;
        $this->modeReservationRepo = $modeReservationRepo;
        $this->reservRepo = $reservRepo;
        $this->reservationHelper = $reservationHelper;
        $this->reservationSession = $reservationSession;
    }

    public function reserver(Devis $devis, $stripeSessionId = "null", $existingDevisBdd = false)
    {

        $devisOptions = $devis->getDevisOptions();

        $reservation = new Reservation();

        $reservation->setVehicule($devis->getVehicule());
        $reservation->setStripeSessionId($stripeSessionId);
        $reservation->setClient($devis->getClient());
        $reservation->setDateDebut($devis->getDateDepart());
        $reservation->setDateFin($devis->getDateRetour());
        $reservation->setAgenceDepart($devis->getAgenceDepart());
        $reservation->setAgenceRetour($devis->getAgenceRetour());
        $reservation->setNumDevis($devis->getId()); //reference numero devis reservé
        //boucle pour ajout options 
        foreach ($devis->getOptions() as $option) {
            $reservation->addOption($option);
        }

        //boucle pour ajout garantie 
        foreach ($devis->getGaranties() as $garantie) {
            $reservation->addGaranty($garantie);
        }

        // $reservation->setPrixOptions($this->tarifsHelper->sommeTarifsOptions($devis->getOptions(), $devis->getConducteur()));
        // $reservation->setPrixGaranties($this->tarifsHelper->sommeTarifsGaranties($devis->getGaranties()));
        // $reservation->setDuree($this->dateHelper->calculDuree($devis->getDateDepart(), $devis->getDateRetour()));
        // $reservation->setTarifVehicule($this->tarifsHelper->calculTarifVehicule($devis->getDateDepart(), $devis->getDateRetour(), $devis->getVehicule()));

        $reservation->setPrixOptions($devis->getPrixOptions());
        $reservation->setPrixGaranties($devis->getPrixGaranties());
        $reservation->setDuree($devis->getDuree());
        $reservation->setTarifVehicule($devis->getTarifVehicule());

        $reservation->setPrix($devis->getPrix());

        $reservation->setDateReservation($this->dateHelper->dateNow());
        $reservation->setCodeReservation('devisTransformé');
        $reservation->setArchived(false);
        $reservation->setCanceled(false);
        $reservation->setNumDevis($devis->getNumero());
        if ($devis->getConducteur()) {

            $reservation->setConducteur(true);
        } else {
            $reservation->setConducteur(false);
        }
        $reservation->setModeReservation($this->modeReservationRepo->findOneBy(['libelle' => 'CPT']));
        // ajout reference dans Entity RESERVATION (CPTGP + year + month + ID)
        $lastID = $this->reservRepo->findBy(array(), array('id' => 'DESC'), 1);
        if ($lastID == null) {
            $currentID = 1;
        } else {
            $currentID = $lastID[0]->getId() + 1;
        }

        $reservation->setRefRes($reservation->getModeReservation()->getLibelle(), $currentID);
        $devis->setTransformed(true);

        $this->em->persist($reservation);
        if ($existingDevisBdd) {
            $this->em->persist($devis);
        }
        $this->em->flush();

        if ($existingDevisBdd) {
            foreach ($devisOptions as $key => $value) {
                $devisOption = new DevisOption();
                $devisOption->setOpt($value->getOpt());
                $devisOption->setQuantity($value->getQuantity());
                $devisOption->setReservation($reservation);
                $this->em->persist($devisOption);
            }
            // // flush le devis options
            // $this->em->flush();
        } else {
            $reservation = $this->reservationHelper->saveDevisOptions($reservation, $this->reservationSession->getOptions(), $this->em);
        }

        //creer un appel à paiement car n'est pas encore payé 

        $appelPaiement = new AppelPaiement();

        $appelPaiement->setReservation($reservation);
        $appelPaiement->setMontant($reservation->getPrix());
        $appelPaiement->setPayed(false);
        $this->em->persist($appelPaiement);
        $this->em->flush();

        return $reservation;
    }
}
