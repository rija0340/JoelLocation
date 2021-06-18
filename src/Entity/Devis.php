<?php

namespace App\Entity;

use App\Repository\DevisRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DevisRepository::class)
 */
class Devis
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="devis")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Client;

    /**
     * @ORM\ManyToOne(targetEntity=Vehicule::class, inversedBy="devis")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Vehicule;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateDepart;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateRetour;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $agenceDepart;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $agenceRetour;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lieuSejour;

    /**
     * @ORM\Column(type="boolean")
     */
    private $conducteur;

    /**
     * @ORM\Column(type="array")
     */
    private $siege = [];

    /**
     * @ORM\Column(type="array")
     */
    private $garantie = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="float")
     */
    private $duree;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?User
    {
        return $this->Client;
    }

    public function setClient(?User $Client): self
    {
        $this->Client = $Client;

        return $this;
    }

    public function getVehicule(): ?Vehicule
    {
        return $this->Vehicule;
    }

    public function setVehicule(?Vehicule $Vehicule): self
    {
        $this->Vehicule = $Vehicule;

        return $this;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->dateDepart;
    }

    public function setDateDepart(\DateTimeInterface $dateDepart): self
    {
        $this->dateDepart = $dateDepart;

        return $this;
    }

    public function getDateRetour(): ?\DateTimeInterface
    {
        return $this->dateRetour;
    }

    public function setDateRetour(\DateTimeInterface $dateRetour): self
    {
        $this->dateRetour = $dateRetour;

        return $this;
    }

    public function getAgenceDepart(): ?string
    {
        return $this->agenceDepart;
    }

    public function setAgenceDepart(string $agenceDepart): self
    {
        $this->agenceDepart = $agenceDepart;

        return $this;
    }

    public function getAgenceRetour(): ?string
    {
        return $this->agenceRetour;
    }

    public function setAgenceRetour(string $agenceRetour): self
    {
        $this->agenceRetour = $agenceRetour;

        return $this;
    }

    public function getLieuSejour(): ?string
    {
        return $this->lieuSejour;
    }

    public function setLieuSejour(string $lieuSejour): self
    {
        $this->lieuSejour = $lieuSejour;

        return $this;
    }

    public function getConducteur(): ?bool
    {
        return $this->conducteur;
    }

    public function setConducteur(bool $conducteur): self
    {
        $this->conducteur = $conducteur;

        return $this;
    }

    public function getSiege(): ?array
    {
        return $this->siege;
    }

    public function setSiege(array $siege): self
    {
        $this->siege = $siege;

        return $this;
    }

    public function getGarantie(): ?array
    {
        return $this->garantie;
    }

    public function setGarantie(array $garantie): self
    {
        $this->garantie = $garantie;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDuree(): ?float
    {
        return $this->duree;
    }

    public function setDuree(float $duree): self
    {
        $this->duree = $duree;

        return $this;
    }
}
