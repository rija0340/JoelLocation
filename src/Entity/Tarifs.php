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
     * @ORM\Column(type="float", nullable=true)
     */
    private $troisJours;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $septJours;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $quinzeJours;

    /**
     * @ORM\Column(type="float",  nullable=true)
     */
    private $trenteJours;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mois;

    /**
     * @ORM\ManyToOne(targetEntity=Marque::class, inversedBy="tarifs")
     */
    private $marque;

    /**
     * @ORM\ManyToOne(targetEntity=Modele::class, inversedBy="tarifs")
     */
    private $modele;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTroisJours(): ?float
    {
        return $this->troisJours;
    }

    public function setTroisJours(?float $troisJours): self
    {
        $this->troisJours = $troisJours;

        return $this;
    }

    public function getSeptJours(): ?float
    {
        return $this->septJours;
    }

    public function setSeptJours(?float $septJours): self
    {
        $this->septJours = $septJours;

        return $this;
    }

    public function getQuinzeJours(): ?int
    {
        return $this->quinzeJours;
    }

    public function setQuinzeJours(?float $quinzeJours): self
    {
        $this->quinzeJours = $quinzeJours;

        return $this;
    }

    public function getTrenteJours(): ?float
    {
        return $this->trenteJours;
    }

    public function setTrenteJours(?float $trenteJours): self
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

    public function getMarque(): ?Marque
    {
        return $this->marque;
    }

    public function setMarque(?Marque $marque): self
    {
        $this->marque = $marque;

        return $this;
    }

    public function getModele(): ?Modele
    {
        return $this->modele;
    }

    public function setModele(?Modele $modele): self
    {
        $this->modele = $modele;

        return $this;
    }
}
