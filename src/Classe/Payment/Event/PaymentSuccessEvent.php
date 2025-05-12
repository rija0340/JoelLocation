<?php

namespace App\Classe\Payment\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\Entity\Devis;
use App\Entity\Reservation;

class PaymentSuccessEvent extends Event
{
    public const NAME = 'payment.success';

    protected Devis $devis;
    protected $data;
    protected $reservation;

    public function __construct(Devis $devis, array $data = [])
    {
        $this->devis = $devis;
        $this->data = $data;
    }

    public function getDevis(): Devis
    {
        return $this->devis;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setReservation(Reservation $reservation): self
    {
        $this->reservation = $reservation;
        return $this;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }
}
