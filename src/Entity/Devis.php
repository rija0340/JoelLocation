<?php

namespace App\Entity;

use App\Repository\DevisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\OptionsGarantiesInterface;
// DON'T forget the following use statement!!!
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * @ORM\Entity(repositoryClass=DevisRepository::class)
 * @UniqueEntity(fields={"client","vehicule", "dateDepart", "dateRetour"})
 */
class Devis implements OptionsGarantiesInterface
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
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity=Vehicule::class, inversedBy="devis",)
     * 
     */
    private $vehicule;

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
     * @ORM\Column(type="string", length=255, nullable = true)
     */
    private $lieuSejour;

    /**
     * @ORM\Column(type="boolean")
     */
    private $conducteur;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="float")
     */
    private $duree;

    /**
     * @ORM\Column(type="float")
     */
    private $prix;
    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $numero;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $transformed;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $tarifVehicule;

    /**
     * @ORM\ManyToMany(targetEntity=Options::class, inversedBy="devis")
     */
    private $options;

    /**
     * @ORM\ManyToMany(targetEntity=Garantie::class, inversedBy="devis")
     */
    private $garanties;

    /**
     * @ORM\Column(type="float")
     */
    private $prixOptions;

    /**
     * @ORM\Column(type="float")
     */
    private $prixGaranties;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $stripeSessionId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $downloadId;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $payement_percentage;

    /**
     * @ORM\OneToMany(targetEntity=DevisOption::class, mappedBy="devis")
     */
    private $devisOptions;

    // public $serializedOptions;
    // public $serializer;
    // Tous les prix sont en ttc
    public function __construct()
    {
        $this->options = new ArrayCollection();
        $this->garanties = new ArrayCollection();
        // $this->serializer = $serializer;
        $this->devisOptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getVehicule(): ?vehicule
    {
        return $this->vehicule;
    }

    public function setVehicule(?vehicule $vehicule): self
    {
        $this->vehicule = $vehicule;

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

    public function setLieuSejour(?string $lieuSejour): self
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

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }



    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function setNumeroDevis($currentID)
    {
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

        $dateNow = new \DateTime('now');

        $currentYear = (str_split($dateNow->format('Y'), 2))[1];

        $ref  = "DV" . $currentYear . $currentID;
        $this->setNumero($ref);
    }

    public function getTransformed(): ?bool
    {
        return $this->transformed;
    }

    public function setTransformed(?bool $transformed): self
    {
        $this->transformed = $transformed;

        return $this;
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

    public function setStripeSessionId(?string $stripeSessionId): self
    {
        $this->stripeSessionId = $stripeSessionId;

        return $this;
    }

    public function getDownloadId(): ?string
    {
        return $this->downloadId;
    }

    public function setDownloadId(?string $downloadId): self
    {
        $this->downloadId = $downloadId;

        return $this;
    }

    // public function serializeOptons()
    // {
    //     $this->serializer->serialize($this->options, 'json');
    //     return $this->serializedOptions;
    // }

    public function getPayementPercentage(): ?float
    {
        return $this->payement_percentage;
    }

    public function setPayementPercentage(?float $payement_percentage): self
    {
        $this->payement_percentage = $payement_percentage;

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
            $devisOption->setDevis($this);
        }

        return $this;
    }

    public function removeDevisOption(DevisOption $devisOption): self
    {
        if ($this->devisOptions->removeElement($devisOption)) {
            // set the owning side to null (unless already changed)
            if ($devisOption->getDevis() === $this) {
                $devisOption->setDevis(null);
            }
        }

        return $this;
    }
}
