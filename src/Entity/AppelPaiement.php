<?php

namespace App\Entity;

use App\Repository\AppelPaiementRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AppelPaiementRepository::class)
 */
class AppelPaiement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Reservation::class)
     */
    private $reservation;

    /**
     * @ORM\Column(type="float")
     */
    private $montant;

    /**
     * @ORM\Column(type="datetime" ,nullable = true)
     */
    private $dateDemande;

    /**
     * @ORM\Column(type="boolean" )
     */
    private $payed;

    /**
     * @ORM\Column(type="datetime",nullable = true)
     */
    private $datePaiement;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @var array|null
     */
    private $sentDates = [];


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        $this->reservation = $reservation;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(\DateTimeInterface $dateDemande): self
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

    public function getPayed(): ?bool
    {
        return $this->payed;
    }

    public function setPayed(bool $payed): self
    {
        $this->payed = $payed;

        return $this;
    }

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->datePaiement;
    }

    public function setDatePaiement(\DateTimeInterface $datePaiement): self
    {
        $this->datePaiement = $datePaiement;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

   public function getSentDates(): array
{
    $dates = [];

    foreach ($this->sentDates ?? [] as $d) {
        $dates[] = new \DateTime($d);
    }

    return $dates;
}

    public function addSentDate(\DateTimeInterface $date): self
    {
        $this->sentDates[] = $date->format('Y-m-d H:i:s');
        return $this;
    }


}
