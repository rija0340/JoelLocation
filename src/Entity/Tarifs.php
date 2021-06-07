<?php

namespace App\Entity;

use App\Repository\TarifsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TarifsRepository::class)
 */
class Tarifs
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $troisJours;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $septJours;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quinzeJours;

    /**
     * @ORM\Column(type="integer",  nullable=true)
     */
    private $trenteJours;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mois;

    /**
     * @ORM\ManyToOne(targetEntity=Vehicule::class, inversedBy="tarifs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $vehicule;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTroisJours(): ?int
    {
        return $this->troisJours;
    }

    public function setTroisJours(?int $troisJours): self
    {
        $this->troisJours = $troisJours;

        return $this;
    }

    public function getSeptJours(): ?int
    {
        return $this->septJours;
    }

    public function setSeptJours(?int $septJours): self
    {
        $this->septJours = $septJours;

        return $this;
    }

    public function getQuinzeJours(): ?int
    {
        return $this->quinzeJours;
    }

    public function setQuinzeJours(?int $quinzeJours): self
    {
        $this->quinzeJours = $quinzeJours;

        return $this;
    }

    public function getTrenteJours(): ?int
    {
        return $this->trenteJours;
    }

    public function setTrenteJours(?int $trenteJours): self
    {
        $this->trenteJours = $trenteJours;

        return $this;
    }

    public function getMois(): ?string
    {
        return $this->mois;
    }

    public function setMois(string $mois): self
    {
        $this->mois = $mois;

        return $this;
    }

    public function getVehicule(): ?Vehicule
    {
        return $this->vehicule;
    }

    public function setVehicule(Vehicule $vehicule): self
    {
        $this->vehicule = $vehicule;

        return $this;
    }
}
