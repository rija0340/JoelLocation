<?php

namespace App\Event;

use App\Entity\Reservation;
use Symfony\Contracts\EventDispatcher\Event;

class ContractSignedByClientEvent extends Event
{
    public const NAME = 'client.contract.signed';

    private Reservation $reservation;
    private string $adminLink;

    public function __construct(Reservation $reservation, string $adminLink)
    {
        $this->reservation = $reservation;
        $this->adminLink = $adminLink;
    }

    public function getReservation(): Reservation
    {
        return $this->reservation;
    }

    public function getAdminLink(): string
    {
        return $this->adminLink;
    }
}