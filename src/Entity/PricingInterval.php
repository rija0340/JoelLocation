<?php

namespace App\Entity;

use App\Repository\PricingIntervalRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PricingIntervalRepository::class)
 * @ORM\Table(name="pricing_interval")
 * @ORM\HasLifecycleCallbacks()
 */
class PricingInterval
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $minDays;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxDays;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $label;

    /**
     * @ORM\Column(type="integer")
     */
    private $sortOrder = 0;

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

    public function getMinDays(): ?int
    {
        return $this->minDays;
    }

    public function setMinDays(int $minDays): self
    {
        $this->minDays = $minDays;
        return $this;
    }

    public function getMaxDays(): ?int
    {
        return $this->maxDays;
    }

    public function setMaxDays(?int $maxDays): self
    {
        $this->maxDays = $maxDays;
        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
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
     * Generate label from min/max days
     */
    public function generateLabel(): string
    {
        if ($this->maxDays === null) {
            return $this->minDays . '+ jours';
        }
        return $this->minDays . '-' . $this->maxDays . ' jours';
    }

    /**
     * Get display label with j abbreviation
     */
    public function getDisplayLabel(): string
    {
        if ($this->maxDays === null) {
            return $this->minDays . '+j';
        }
        return $this->minDays . '-' . $this->maxDays . 'j';
    }
}
