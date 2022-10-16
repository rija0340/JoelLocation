<?php

namespace App\Entity;

use App\Repository\ConducteurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConducteurRepository::class)
 */
class Conducteur
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateNaissance;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $villeNaissance;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $numeroPermis;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $villeDelivrance;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateDelivrance;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateExpiration;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $prenom;



    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="conducteurs")
     */
    private $client;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPrincipal;

    /**
     * @ORM\ManyToMany(targetEntity=Reservation::class, mappedBy="conducteursClient")
     */
    private $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTimeInterface $dateNaissance): self
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getVilleNaissance(): ?string
    {
        return $this->villeNaissance;
    }

    public function setVilleNaissance(?string $villeNaissance): self
    {
        $this->villeNaissance = $villeNaissance;

        return $this;
    }

    public function getNumeroPermis(): ?string
    {
        return $this->numeroPermis;
    }

    public function setNumeroPermis(?string $numeroPermis): self
    {
        $this->numeroPermis = $numeroPermis;

        return $this;
    }

    public function getVilleDelivrance(): ?string
    {
        return $this->villeDelivrance;
    }

    public function setVilleDelivrance(?string $villeDelivrance): self
    {
        $this->villeDelivrance = $villeDelivrance;

        return $this;
    }

    public function getDateDelivrance(): ?\DateTimeInterface
    {
        return $this->dateDelivrance;
    }

    public function setDateDelivrance(?\DateTimeInterface $dateDelivrance): self
    {
        $this->dateDelivrance = $dateDelivrance;

        return $this;
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(?\DateTimeInterface $dateExpiration): self
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }


    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }


    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getIsPrincipal(): ?bool
    {
        return $this->isPrincipal;
    }

    public function setIsPrincipal(bool $isPrincipal): self
    {
        $this->isPrincipal = $isPrincipal;

        return $this;
    }

    /**
     * @return Collection|Reservation[]
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->addConducteursClient($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            $reservation->removeConducteursClient($this);
        }

        return $this;
    }
}
