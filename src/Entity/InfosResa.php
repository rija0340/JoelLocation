<?php

namespace App\Entity;

use App\Repository\InfosResaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InfosResaRepository::class)
 */
class InfosResa
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $nbrAdultes;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $nbrEnfants;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $nbrBebes;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $infosInternes;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbrAdultes(): ?float
    {
        return $this->nbrAdultes;
    }

    public function setNbrAdultes(?float $nbrAdultes): self
    {
        $this->nbrAdultes = $nbrAdultes;

        return $this;
    }

    public function getNbrEnfants(): ?float
    {
        return $this->nbrEnfants;
    }

    public function setNbrEnfants(?float $nbrEnfants): self
    {
        $this->nbrEnfants = $nbrEnfants;

        return $this;
    }

    public function getNbrBebes(): ?float
    {
        return $this->nbrBebes;
    }

    public function setNbrBebes(?float $nbrBebes): self
    {
        $this->nbrBebes = $nbrBebes;

        return $this;
    }

    public function getInfosInternes(): ?string
    {
        return $this->infosInternes;
    }

    public function setInfosInternes(?string $infosInternes): self
    {
        $this->infosInternes = $infosInternes;

        return $this;
    }
}
