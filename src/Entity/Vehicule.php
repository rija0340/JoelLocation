<?php

namespace App\Entity;

use App\Repository\VehiculeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VehiculeRepository::class)
 */
class Vehicule
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
    private $immatriculation;

    /**
     * @ORM\ManyToOne(targetEntity=Marque::class, inversedBy="vehicules")
     * @ORM\JoinColumn(nullable=false)
     */
    private $marque;

    /**
     * @ORM\ManyToOne(targetEntity=Type::class, inversedBy="vehicules")
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_mise_service;

    /**
     * @ORM\Column(type="date")
     */
    private $date_mise_location;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $modele;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $prix_acquisition;

    /**
     * @ORM\Column(type="integer")
     */
    private $tarif_journaliere;

    /**
     * @ORM\OneToMany(targetEntity=Reservation::class, mappedBy="vehicule", orphanRemoval=true)
     */
    private $reservations;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $details;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $carburation;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $caution;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $vitesse;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bagages;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $portes;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $passagers;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $atouts;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImmatriculation(): ?string
    {
        return $this->immatriculation;
    }

    public function setImmatriculation(string $immatriculation): self
    {
        $this->immatriculation = $immatriculation;

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

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDateMiseService(): ?\DateTimeInterface
    {
        return $this->date_mise_service;
    }

    public function setDateMiseService(?\DateTimeInterface $date_mise_service): self
    {
        $this->date_mise_service = $date_mise_service;

        return $this;
    }

    public function getDateMiseLocation(): ?\DateTimeInterface
    {
        return $this->date_mise_location;
    }

    public function setDateMiseLocation(\DateTimeInterface $date_mise_location): self
    {
        $this->date_mise_location = $date_mise_location;

        return $this;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(string $modele): self
    {
        $this->modele = $modele;

        return $this;
    }

    public function getPrixAcquisition(): ?int
    {
        return $this->prix_acquisition;
    }

    public function setPrixAcquisition(?int $prix_acquisition): self
    {
        $this->prix_acquisition = $prix_acquisition;

        return $this;
    }

    public function getTarifJournaliere(): ?int
    {
        return $this->tarif_journaliere;
    }

    public function setTarifJournaliere(int $tarif_journaliere): self
    {
        $this->tarif_journaliere = $tarif_journaliere;

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
            $reservation->setVehicule($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getVehicule() === $this) {
                $reservation->setVehicule(null);
            }
        }

        return $this;
    }

    /**
    * toString
    * @return string
    */
    public function __toString()
    {
        return $this->getImmatriculation();
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getCarburation(): ?string
    {
        return $this->carburation;
    }

    public function setCarburation(?string $carburation): self
    {
        $this->carburation = $carburation;

        return $this;
    }

    public function getCaution(): ?float
    {
        return $this->caution;
    }

    public function setCaution(?float $caution): self
    {
        $this->caution = $caution;

        return $this;
    }

    public function getVitesse(): ?string
    {
        return $this->vitesse;
    }

    public function setVitesse(?string $vitesse): self
    {
        $this->vitesse = $vitesse;

        return $this;
    }

    public function getBagages(): ?string
    {
        return $this->bagages;
    }

    public function setBagages(?string $bagages): self
    {
        $this->bagages = $bagages;

        return $this;
    }

    public function getPortes(): ?string
    {
        return $this->portes;
    }

    public function setPortes(?string $portes): self
    {
        $this->portes = $portes;

        return $this;
    }

    public function getPassagers(): ?string
    {
        return $this->passagers;
    }

    public function setPassagers(?string $passagers): self
    {
        $this->passagers = $passagers;

        return $this;
    }

    public function getAtouts(): ?string
    {
        return $this->atouts;
    }

    public function setAtouts(?string $atouts): self
    {
        $this->atouts = $atouts;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
