<?php

namespace App\Service;

use App\Entity\Paiement;
use App\Entity\Reservation;
use App\Repository\ModePaiementRepository;
use Doctrine\ORM\EntityManagerInterface;

class PaymentProcessingService
{
    private $em;
    private $modePaiementRepo;
    private $reservationHelper;
    private $dateHelper;

    public function __construct(
        EntityManagerInterface $em,
        ModePaiementRepository $modePaiementRepo,
        ReservationHelper $reservationHelper,
        DateHelper $dateHelper
    ) {
        $this->em = $em;
        $this->modePaiementRepo = $modePaiementRepo;
        $this->reservationHelper = $reservationHelper;
        $this->dateHelper = $dateHelper;
    }

    /**
     * Add a payment to a reservation
     */
    public function addPaymentToReservation(
        Reservation $reservation, 
        float $amount, 
        \DateTime $datePaiement, 
        int $modePaiementId, 
        string $motif = "Réservation"
    ): bool {
        // Check if the total payments would exceed the total due
        $totalPaiements = $reservation->getSommePaiements();
        $totalDue = $this->reservationHelper->getTotalResaFraisTTC($reservation);

        if ($totalPaiements + $amount > $totalDue) {
            return false; // Payment would exceed total due
        }

        // Create new payment
        $paiement = new Paiement();
        $paiement->setClient($reservation->getClient());
        $paiement->setDatePaiement($datePaiement);
        $paiement->setMontant($amount);
        $paiement->setReservation($reservation);
        $paiement->setModePaiement($this->modePaiementRepo->find($modePaiementId));
        $paiement->setMotif($motif);
        $paiement->setCreatedAt($this->dateHelper->dateNow());

        $this->em->persist($paiement);
        $this->em->flush();

        return true;
    }

    /**
     * Check if payments exceed the total due for a reservation
     */
    public function isPaymentAmountValid(Reservation $reservation, float $additionalAmount): bool
    {
        $totalPaiements = $reservation->getSommePaiements();
        $totalDue = $this->reservationHelper->getTotalResaFraisTTC($reservation);

        return ($totalPaiements + $additionalAmount) <= $totalDue;
    }

    /**
     * Calculate remaining amount due for a reservation
     */
    public function calculateRemainingAmount(Reservation $reservation): float
    {
        $totalDue = $this->reservationHelper->getTotalResaFraisTTC($reservation);
        $totalPaid = $reservation->getSommePaiements();

        return max(0, $totalDue - $totalPaid);
    }
}