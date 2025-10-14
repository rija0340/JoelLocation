<?php

namespace App\Service;

use App\Entity\AnnulationReservation;
use App\Entity\Reservation;
use App\Repository\AnnulationReservationRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ReservationStateService
{
    private $reservationRepo;
    private $annulationResaRepo;
    private $em;
    private $dateHelper;
    private $tarifsHelper;
    private $emailManagerService;

    public function __construct(
        ReservationRepository $reservationRepo,
        AnnulationReservationRepository $annulationResaRepo,
        EntityManagerInterface $em,
        DateHelper $dateHelper,
        TarifsHelper $tarifsHelper,
        EmailManagerService $emailManagerService
    ) {
        $this->reservationRepo = $reservationRepo;
        $this->annulationResaRepo = $annulationResaRepo;
        $this->em = $em;
        $this->dateHelper = $dateHelper;
        $this->tarifsHelper = $tarifsHelper;
        $this->emailManagerService = $emailManagerService;
    }

    /**
     * Cancel a reservation
     */
    public function cancelReservation(Request $request,Reservation $reservation, AnnulationReservation $annulation, ?float $montant = null): bool
    {
        // Check if reservation is already cancelled
        $annulResa = $this->annulationResaRepo->findOneBy(['reservation' => $reservation]);

        if ($annulResa !== null) {
            return false; // Already cancelled
        }
        
        $annulation->setReservation($reservation);
        $annulation->setCreatedAt($this->dateHelper->dateNow());
        $annulation->setMontantAvoir($montant);
        $this->em->persist($annulation);
        $reservation->setCanceled(true);
        $this->em->flush();
        if(!is_null($montant)){
            $this->emailManagerService->sendAvoir($request, $reservation, $montant);
        }
        
        return true;
    }

    /**
     * Report (reschedule) a reservation
     */
    public function reportReservation(Reservation $reservation, ?\DateTime $newDateDebut = null, ?\DateTime $newDateFin = null): bool
    {
        // Use provided dates or keep existing ones
        $dateDepart = $newDateDebut ?: $reservation->getDateDebut();
        $dateRetour = $newDateFin ?: $reservation->getDateFin();

        $duree = $this->dateHelper->calculDuree($dateDepart, $dateRetour);
        $tarifVehicule = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $reservation->getVehicule());

        $reservation->setReported(true);
        $reservation->setDuree($duree);
        $reservation->setDateDebut($dateDepart);
        $reservation->setDateFin($dateRetour);
        $reservation->setTarifVehicule($tarifVehicule);
        $reservation->setPrix($tarifVehicule + $reservation->getPrixGaranties() + $reservation->getPrixOptions());

        $this->em->flush();

        return true;
    }

    /**
     * Archive a reservation
     */
    public function archiveReservation(Reservation $reservation): void
    {
        $reservation->setArchived(true);
        $this->em->flush();
    }

    /**
     * Process early return (anticipated return)
     */
    public function processEarlyReturn(Reservation $reservation): void
    {
        $reservation->setDateFin($this->dateHelper->dateNow());
        $reservation->setDuree($this->dateHelper->calculDuree($reservation->getDateDebut(), $reservation->getDateFin()));
        $this->em->flush();
    }
}