<?php

namespace App\Entity;

use App\Repository\TarifsV2CellRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TarifsV2CellRepository::class)
 * @ORM\Table(name="tarifs_v2_cell", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_cell", columns={"marque_id", "modele_id", "month", "pricing_interval_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class TarifsV2Cell
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Marque::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $marque;

    /**
     * @ORM\ManyToOne(targetEntity=Modele::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $modele;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $month;

    /**
     * @ORM\ManyToOne(targetEntity=PricingInterval::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $pricingInterval;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMonth(): ?string
    {
        return $this->month;
    }

    public function setMonth(string $month): self
    {
        $this->month = $month;
        return $this;
    }

    public function getPricingInterval(): ?PricingInterval
    {
        return $this->pricingInterval;
    }

    public function setPricingInterval(?PricingInterval $pricingInterval): self
    {
        $this->pricingInterval = $pricingInterval;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * Get vehicle identifier string
     */
    public function getVehicleName(): string
    {
        return $this->marque->getLibelle() . ' ' . $this->modele->getLibelle();
    }
}
