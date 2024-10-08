<?php

namespace App\Classe;

use App\Entity\Devis;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Service\TarifsHelper;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ModeReservationRepository;
use App\Repository\ReservationRepository;

class ReserverDevis
{
    private $em;
    private $tarifsHelper;
    private $dateHelper;
    private $modeReservationRepo;
    private $reservRepo;

    public function __construct(
        EntityManagerInterface $em,
        TarifsHelper $tarifsHelper,
        DateHelper $dateHelper,
        ModeReservationRepository $modeReservationRepo,
        ReservationRepository $reservRepo
    ) {
        $this->em = $em;
        $this->tarifsHelper = $tarifsHelper;
        $this->dateHelper = $dateHelper;
        $this->modeReservationRepo = $modeReservationRepo;
        $this->reservRepo = $reservRepo;
    }

    public function reserver(Devis $devis, $stripeSessionId = "null")
    {
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
        $this->em->persist($devis);
        $this->em->flush();


        return $reservation;
    }
}
