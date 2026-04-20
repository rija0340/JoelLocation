<?php

namespace App\Entity;

use App\Repository\MarqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MarqueRepository::class)
 */
class Marque
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
    private $libelle;


    /**
     * @ORM\OneToMany(targetEntity=Modele::class, mappedBy="marque")
     */
    private $modeles;

    /**
     * @ORM\OneToMany(targetEntity=Vehicule::class, mappedBy="marque")
     */
    private $vehicules;

    /**
     * @ORM\OneToMany(targetEntity=Tarifs::class, mappedBy="marque")
     */
    private $tarifs;

    /**
     * @ORM\OneToMany(targetEntity=TarifsV2::class, mappedBy="marque")
     */
    private $tarifsV2;

    public function __construct()
    {
        $this->vehicules = new ArrayCollection();
        $this->modeles = new ArrayCollection();
        $this->tarifs = new ArrayCollection();
        $this->tarifsV2 = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection|Vehicule[]
     */
    public function getVehicules(): Collection
    {
        return $this->vehicules;
    }

    /**
     * toString
     * @return string
     */
    public function __toString()
    {
        return $this->getLibelle();
    }

    /**
     * @return Collection|Modele[]
     */
    public function getModeles(): Collection
    {
        return $this->modeles;
    }

    public function addModele(Modele $modele): self
    {
        if (!$this->modeles->contains($modele)) {
            $this->modeles[] = $modele;
            $modele->setMarque($this);
        }

        return $this;
    }

    public function removeModele(Modele $modele): self
    {
        if ($this->modeles->removeElement($modele)) {
            // set the owning side to null (unless already changed)
            if ($modele->getMarque() === $this) {
                $modele->setMarque(null);
            }
        }

        return $this;
    }

    public function addVehicule(Vehicule $vehicule): self
    {
        if (!$this->vehicules->contains($vehicule)) {
            $this->vehicules[] = $vehicule;
            $vehicule->setMarque($this);
        }

        return $this;
    }

    public function removeVehicule(Vehicule $vehicule): self
    {
        if ($this->vehicules->removeElement($vehicule)) {
            // set the owning side to null (unless already changed)
            if ($vehicule->getMarque() === $this) {
                $vehicule->setMarque(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Tarifs[]
     */
    public function getTarifs(): Collection
    {
        return $this->tarifs;
    }

    public function addTarif(Tarifs $tarif): self
    {
        if (!$this->tarifs->contains($tarif)) {
            $this->tarifs[] = $tarif;
            $tarif->setMarque($this);
        }

        return $this;
    }

    public function removeTarif(Tarifs $tarif): self
    {
        if ($this->tarifs->removeElement($tarif)) {
            // set the owning side to null (unless already changed)
            if ($tarif->getMarque() === $this) {
                $tarif->setMarque(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TarifsV2[]
     */
    public function getTarifsV2(): Collection
    {
        return $this->tarifsV2;
    }

    public function addTarifsV2(TarifsV2 $tarifsV2): self
    {
        if (!$this->tarifsV2->contains($tarifsV2)) {
            $this->tarifsV2[] = $tarifsV2;
            $tarifsV2->setMarque($this);
        }

        return $this;
    }

    public function removeTarifsV2(TarifsV2 $tarifsV2): self
    {
        if ($this->tarifsV2->removeElement($tarifsV2)) {
            if ($tarifsV2->getMarque() === $this) {
                $tarifsV2->setMarque(null);
            }
        }

        return $this;
    }
}
