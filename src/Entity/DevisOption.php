<?php

namespace App\Entity;

use App\Repository\DevisOptionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DevisOptionRepository::class)
 */
class DevisOption
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Devis::class, inversedBy="devisOptions")
     * @ORM\JoinColumn(nullable=true)
     */
    private $devis;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Reservation", inversedBy="devisOptions")
     * @ORM\JoinColumn(nullable=true) 
     */
    private $reservation;

    /**
     * @ORM\ManyToOne(targetEntity=Options::class, inversedBy="devisOptions")
     */
    private $opt;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDevis(): ?Devis
    {
        return $this->devis;
    }

    public function setDevis(?Devis $devis): self
    {
        $this->devis = $devis;

        return $this;
    }

    public function getOpt(): ?Options
    {
        return $this->opt;
    }

    public function setOpt(?Options $opt): self
    {
        $this->opt = $opt;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
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
}
