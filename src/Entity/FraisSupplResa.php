<?php

namespace App\Entity;

use App\Repository\FraisSupplResaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FraisSupplResaRepository::class)
 */
class FraisSupplResa
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="float")
     */
    private $prixUnitaire;

    /**
     * @ORM\Column(type="float")
     */
    private $quantite;

    /**
     * @ORM\Column(type="float", nullable = true)
     */
    private $remise;

    /**
     * @ORM\Column(type="float")
     */
    private $totalTTC;

    /**
     * @ORM\ManyToOne(targetEntity=reservation::class, inversedBy="fraisSupplResas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $reservation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrixUnitaire(): ?float
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(float $prixUnitaire): self
    {
        $this->prixUnitaire = $prixUnitaire;

        return $this;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(float $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getRemise(): ?float
    {
        return $this->remise;
    }

    public function setRemise(float $remise): self
    {
        $this->remise = $remise;

        return $this;
    }

    public function getTotalTTC(): ?float
    {
        return $this->totalTTC;
    }

    public function setTotalTTC(float $totalTTC): self
    {
        $this->totalTTC = $totalTTC;

        return $this;
    }

    public function getReservation(): ?reservation
    {
        return $this->reservation;
    }

    public function setReservation(?reservation $reservation): self
    {
        $this->reservation = $reservation;

        return $this;
    }

    public function __toString()
    {
        return $this->description;
    }

    /**
     * Retourne la désignation (alias pour description)
     */
    public function getDesignation(): ?string
    {
        return $this->description;
    }

    /**
     * Calcule le prix HT à partir du TTC
     * TVA Guadeloupe = 8.5%
     */
    public function getTotalHT(): float
    {
        $taxe = 0.085;
        return $this->totalTTC / (1 + $taxe);
    }
}
