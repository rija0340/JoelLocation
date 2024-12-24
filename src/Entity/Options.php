<?php

namespace App\Entity;

use App\Repository\OptionsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OptionsRepository::class)
 */
class Options
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
     * @ORM\ManyToMany(targetEntity=Devis::class, mappedBy="options")
     */
    private $devis;

    /**
     * @ORM\ManyToMany(targetEntity=Reservation::class, mappedBy="options")
     */
    private $reservations;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=DevisOption::class, mappedBy="opt")
     */
    private $devisOptions;



    public function __construct()
    {
        $this->devis = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->y = new ArrayCollection();
        $this->devisOptions = new ArrayCollection();
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
            $devi->addOption($this);
        }

        return $this;
    }

    public function removeDevi(Devis $devi): self
    {
        if ($this->devis->removeElement($devi)) {
            $devi->removeOption($this);
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
            $reservation->addOption($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            $reservation->removeOption($this);
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, DevisOption>
     */
    public function getDevisOptions(): Collection
    {
        return $this->devisOptions;
    }

    public function addDevisOption(DevisOption $devisOption): self
    {
        if (!$this->devisOptions->contains($devisOption)) {
            $this->devisOptions[] = $devisOption;
            $devisOption->setOpt($this);
        }

        return $this;
    }

    public function removeDevisOption(DevisOption $devisOption): self
    {
        if ($this->devisOptions->removeElement($devisOption)) {
            // set the owning side to null (unless already changed)
            if ($devisOption->getOpt() === $this) {
                $devisOption->setOpt(null);
            }
        }

        return $this;
    }
}
