<?php

namespace App\Entity;

use App\Repository\TarifsV2Repository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TarifsV2Repository::class)
 * @ORM\Table(name="tarifs_v2")
 */
class TarifsV2
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Marque::class, inversedBy="tarifsV2")
     * @ORM\JoinColumn(nullable=false)
     */
    private $marque;

    /**
     * @ORM\ManyToOne(targetEntity=Modele::class, inversedBy="tarifsV2")
     * @ORM\JoinColumn(nullable=false)
     */
    private $modele;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mois;

    /**
     * @ORM\Column(type="json")
     * Stores array of pricing ranges:
     * [
     *   {"min_days": 1, "max_days": 5, "price": 45.00},
     *   {"min_days": 6, "max_days": 10, "price": 40.00},
     *   ...
     * ]
     */
    private $tarifs;

    public function __construct()
    {
        $this->tarifs = [];
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

    public function getMois(): ?string
    {
        return $this->mois;
    }

    public function setMois(string $mois): self
    {
        $this->mois = $mois;
        return $this;
    }

    public function getTarifs(): array
    {
        return $this->tarifs;
    }

    public function setTarifs(array $tarifs): self
    {
        $this->tarifs = $tarifs;
        return $this;
    }

    /**
     * Add a pricing range
     */
    public function addTarif(int $minDays, int $maxDays, float $price): self
    {
        $this->tarifs[] = [
            'min_days' => $minDays,
            'max_days' => $maxDays,
            'price' => $price,
        ];
        return $this;
    }

    /**
     * Get price for a specific duration
     */
    public function getPrixForDays(int $days): ?float
    {
        foreach ($this->tarifs as $tarif) {
            if ($days >= $tarif['min_days'] && $days <= $tarif['max_days']) {
                return (float) $tarif['price'];
            }
        }
        return null;
    }

    /**
     * Validate that ranges don't overlap
     */
    public function hasOverlappingRanges(): bool
    {
        $ranges = $this->tarifs;
        usort($ranges, function($a, $b) {
            return $a['min_days'] <=> $b['min_days'];
        });

        for ($i = 0; $i < count($ranges) - 1; $i++) {
            if ($ranges[$i]['max_days'] >= $ranges[$i + 1]['min_days']) {
                return true;
            }
        }
        return false;
    }
}
