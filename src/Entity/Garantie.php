<?php

namespace App\Entity;

use App\Repository\GarantieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GarantieRepository::class)
 */
class Garantie
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
    private $appelation;

    /**
     * @ORM\Column(type="float")
     */
    private $prix;



    /**
     * @ORM\Column(type="text")
     */
    private $description;


    /**
     * @ORM\ManyToMany(targetEntity=Devis::class, mappedBy="garanties")
     */
    private $devis;

    /**
     * @ORM\ManyToMany(targetEntity=Reservation::class, mappedBy="garanties")
     */
    private $reservations;

    public function __construct()
    {
        $this->devis = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppelation(): ?string
    {
        return $this->appelation;
    }

    public function setAppelation(string $appelation): self
    {
        $this->appelation = $appelation;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
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



    /**
     * toString
     * @return string
     */
    public function __toString()
    {
        return $this->getAppelation();
    }


    /**
     * @return Collection|Devis[]
     */
    public function getDevis(): Collection
    {
        return $this->devis;
    }

    public function addDevi(Devis $devi): self
    {
        if (!$this->devis->contains($devi)) {
            $this->devis[] = $devi;
            $devi->addGaranty($this);
        }

        return $this;
    }

    public function removeDevi(Devis $devi): self
    {
        if ($this->devis->removeElement($devi)) {
            $devi->removeGaranty($this);
        }

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
            $reservation->addGaranty($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            $reservation->removeGaranty($this);
        }

        return $this;
    }
}
