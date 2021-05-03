<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ReservationRepository::class)
 */
class Reservation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("reserv:read")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("reserv:read")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="reservations")
     * @ORM\JoinColumn(nullable=false)
     * 
     */
    private $client;

    /**
     * @ORM\Column(type="datetime")
     * 
     */
    private $date_reservation;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("reserv:read")
     */
    private $date_debut;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("reserv:read")
     */
    private $date_fin;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     */
    private $lieu;

    /**
     * @ORM\ManyToOne(targetEntity=Vehicule::class, inversedBy="reservations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $vehicule;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     */
    private $code_reservation;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="utilisateur")
     */
    private $utilisateur;

    /**
     * @ORM\ManyToOne(targetEntity=ModeReservation::class, inversedBy="reservations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mode_reservation;

    /**
     * @ORM\ManyToOne(targetEntity=EtatReservation::class, inversedBy="reservations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $etat_reservation;

    /**
     * @ORM\OneToMany(targetEntity=Avis::class, mappedBy="reservation")
     */
    private $avis;

    /**
     * @ORM\OneToMany(targetEntity=Paiement::class, mappedBy="reservation")
     */
    private $paiements;

    public function __construct()
    {
        $this->avis = new ArrayCollection();
        $this->paiements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getDateReservation(): ?\DateTimeInterface
    {
        return $this->date_reservation;
    }

    public function setDateReservation(\DateTimeInterface $date_reservation): self
    {
        $this->date_reservation = $date_reservation;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTimeInterface $date_debut): self
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getVehicule(): ?Vehicule
    {
        return $this->vehicule;
    }

    public function setVehicule(?Vehicule $vehicule): self
    {
        $this->vehicule = $vehicule;

        return $this;
    }

    public function getCodeReservation(): ?string
    {
        return $this->code_reservation;
    }

    public function setCodeReservation(string $code_reservation): self
    {
        $this->code_reservation = $code_reservation;

        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getModeReservation(): ?ModeReservation
    {
        return $this->mode_reservation;
    }

    public function setModeReservation(?ModeReservation $mode_reservation): self
    {
        $this->mode_reservation = $mode_reservation;

        return $this;
    }

    public function getEtatReservation(): ?EtatReservation
    {
        return $this->etat_reservation;
    }

    public function setEtatReservation(?EtatReservation $etat_reservation): self
    {
        $this->etat_reservation = $etat_reservation;

        return $this;
    }

    /**
     * @return Collection|Avis[]
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): self
    {
        if (!$this->avis->contains($avi)) {
            $this->avis[] = $avi;
            $avi->setReservation($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): self
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getReservation() === $this) {
                $avi->setReservation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Paiement[]
     */
    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function addPaiement(Paiement $paiement): self
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements[] = $paiement;
            $paiement->setReservation($this);
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): self
    {
        if ($this->paiements->removeElement($paiement)) {
            // set the owning side to null (unless already changed)
            if ($paiement->getReservation() === $this) {
                $paiement->setReservation(null);
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
        return $this->getCodeReservation();
    }
}
