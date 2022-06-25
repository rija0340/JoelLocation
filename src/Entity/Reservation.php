<?php

namespace App\Entity;

use App\Repository\PaiementRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReservationRepository;
use App\Service\DateHelper;
use App\Service\TarifsHelper;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ReservationRepository::class)
 */
class Reservation
{

    private $tarifsGaranties;
    private $tarifsOptions;

    public function __construct()
    {

        $this->paiements = new ArrayCollection();
        $this->options = new ArrayCollection();
        $this->garanties = new ArrayCollection();
        $this->setReportedFalseValue();
        $this->conducteursClient = new ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("reserv:read")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable = true)
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
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     */
    private $lieu;

    /**
     * @ORM\ManyToOne(targetEntity=Vehicule::class, inversedBy="reservations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $vehicule;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     */
    private $code_reservation;



    /**
     * @ORM\ManyToOne(targetEntity=ModeReservation::class, inversedBy="reservations")
     * @ORM\JoinColumn(nullable=true)
     */
    private $mode_reservation;

    /**
     * @ORM\ManyToOne(targetEntity=EtatReservation::class, inversedBy="reservations")
     * @ORM\JoinColumn(nullable=true)
     */
    private $etat_reservation;

    /**
     * @ORM\OneToMany(targetEntity=Paiement::class, mappedBy="reservation")
     */
    private $paiements;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $commentaire;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $agenceDepart;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $agenceRetour;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $prix;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $duree;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $numDevis;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $reference;


    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $tarifVehicule;

    /**
     * @ORM\ManyToMany(targetEntity=Options::class, inversedBy="reservations")
     */
    private $options;

    /**
     * @ORM\ManyToMany(targetEntity=Garantie::class, inversedBy="reservations")
     */
    private $garanties;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $prixOptions;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $prixGaranties;

    /**
     * @ORM\Column(type="string", length=255, nullable = true)
     */
    private $stripeSessionId;

    private $sommePaiements;

    /**
     * @ORM\OneToOne(targetEntity=Avis::class, mappedBy="reservation", cascade={"persist", "remove"})
     * 
     */
    private $avis;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $archived;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $canceled;



    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $conducteur;

    /**
     * @ORM\Column(type="boolean")
     */
    private $reported;

    /**
     * @ORM\ManyToMany(targetEntity=Conducteur::class, inversedBy="reservations")
     */
    private $conducteursClient;


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

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;

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

    public function getAgenceDepart(): ?string
    {
        return $this->agenceDepart;
    }

    public function setAgenceDepart(?string $agenceDepart): self
    {
        $this->agenceDepart = $agenceDepart;

        return $this;
    }

    public function getAgenceRetour(): ?string
    {
        return $this->agenceRetour;
    }

    public function setAgenceRetour(?string $agenceRetour): self
    {
        $this->agenceRetour = $agenceRetour;

        return $this;
    }


    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getDuree(): ?float
    {
        return $this->duree;
    }

    public function setDuree(?float $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getNumDevis(): ?string
    {
        return $this->numDevis;
    }

    public function setNumDevis(?string $numDevis): self
    {
        $this->numDevis = $numDevis;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    // custom function
    public function setRefRes($pref, $currentID)
    {
        $dateHelper = new DateHelper();
        $currentTime = new \DateTime('NOW');
        $year = $currentTime->format('Y');
        $month = $dateHelper->getMonthName($currentTime);
        if ($currentID < 10) {
            $currentID = '0000' . $currentID;
        }
        if ($currentID < 100 && $currentID > 10) {
            $currentID = '000' . $currentID;
        }
        if ($currentID < 1000 && $currentID > 100) {
            $currentID = '00' . $currentID;
        }
        if ($currentID < 10000 && $currentID > 1000) {
            $currentID = '0' . $currentID;
        }
        $ref  = $pref . $year . $month . $currentID;
        $this->setReference($ref);
    }

    public function frenchMouth()
    {
    }

    public function getTarifVehicule(): ?float
    {
        return $this->tarifVehicule;
    }

    public function setTarifVehicule(?float $tarifVehicule): self
    {
        $this->tarifVehicule = $tarifVehicule;

        return $this;
    }

    /**
     * @return Collection|Options[]
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(Options $option): self
    {
        if (!$this->options->contains($option)) {
            $this->options[] = $option;
        }

        return $this;
    }

    public function removeOption(Options $option): self
    {
        $this->options->removeElement($option);

        return $this;
    }

    /**
     * @return Collection|Garantie[]
     */
    public function getGaranties(): Collection
    {
        return $this->garanties;
    }

    public function addGaranty(Garantie $garanty): self
    {
        if (!$this->garanties->contains($garanty)) {
            $this->garanties[] = $garanty;
        }

        return $this;
    }

    public function removeGaranty(Garantie $garanty): self
    {
        $this->garanties->removeElement($garanty);

        return $this;
    }

    public function getPrixOptions(): ?float
    {
        return $this->prixOptions;
    }

    public function setPrixOptions(?float $prixOptions): self
    {
        $this->prixOptions = $prixOptions;

        return $this;
    }

    public function getPrixGaranties(): ?float
    {
        return $this->prixGaranties;
    }

    public function setPrixGaranties(?float $prixGaranties): self
    {
        $this->prixGaranties = $prixGaranties;

        return $this;
    }

    public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(string $stripeSessionId): self
    {
        $this->stripeSessionId = $stripeSessionId;

        return $this;
    }

    public function getSommePaiements()
    {
        $paiements = $this->getPaiements();
        $this->sommePaiements = 0;
        foreach ($paiements as $paiement) {
            $this->sommePaiements = $this->sommePaiements + $paiement->getMontant();
        }
        // dd($this->sommePaiements);
        return $this->sommePaiements;
    }

    public function getSommeGaranties()
    {
        $somme = 0;
        foreach ($this->getGaranties() as $garantie) {
            $somme = $somme +  $garantie->getPrix();
        }
        return $somme;
    }

    public function getSommeOptions()
    {
        $somme = 0;
        foreach ($this->getOptions() as $option) {
            $somme = $somme +  $option->getPrix();
        }
        return $somme;
    }

    public function getAvis(): ?Avis
    {
        return $this->avis;
    }

    public function setAvis(?Avis $avis): self
    {
        // unset the owning side of the relation if necessary
        if ($avis === null && $this->avis !== null) {
            $this->avis->setReservation(null);
        }

        // set the owning side of the relation if necessary
        if ($avis !== null && $avis->getReservation() !== $this) {
            $avis->setReservation($this);
        }

        $this->avis = $avis;

        return $this;
    }

    public function getArchived(): ?bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): self
    {
        $this->archived = $archived;

        return $this;
    }

    public function getCanceled(): ?bool
    {
        return $this->canceled;
    }

    public function setCanceled(bool $canceled): self
    {
        $this->canceled = $canceled;

        return $this;
    }


    public function getConducteur(): ?bool
    {
        return $this->conducteur;
    }

    public function setConducteur(?bool $conducteur): self
    {
        $this->conducteur = $conducteur;

        return $this;
    }

    public function getReported(): ?bool
    {
        return $this->reported;
    }

    public function setReported(bool $reported): self
    {
        $this->reported = $reported;

        return $this;
    }

    public function setReportedFalseValue()
    {
        $this->setReported(false);
    }

    /**
     * @return Collection|Conducteur[]
     */
    public function getConducteursClient(): Collection
    {
        return $this->conducteursClient;
    }

    public function addConducteursClient(Conducteur $conducteursClient): self
    {
        if (!$this->conducteursClient->contains($conducteursClient)) {
            $this->conducteursClient[] = $conducteursClient;
        }

        return $this;
    }

    public function removeConducteursClient(Conducteur $conducteursClient): self
    {
        $this->conducteursClient->removeElement($conducteursClient);

        return $this;
    }
}
