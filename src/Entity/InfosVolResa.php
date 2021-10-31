<?php

namespace App\Entity;

use App\Repository\InfosVolResaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InfosVolResaRepository::class)
 */
class InfosVolResa
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $compagnieAller;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $compagnieRetour;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $numVolAller;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $numVolRetour;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $heureVolAller;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $heureVolRetour;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompagnieAller(): ?string
    {
        return $this->compagnieAller;
    }

    public function setCompagnieAller(?string $compagnieAller): self
    {
        $this->compagnieAller = $compagnieAller;

        return $this;
    }

    public function getCompagnieRetour(): ?string
    {
        return $this->compagnieRetour;
    }

    public function setCompagnieRetour(?string $compagnieRetour): self
    {
        $this->compagnieRetour = $compagnieRetour;

        return $this;
    }

    public function getNumVolAller(): ?string
    {
        return $this->numVolAller;
    }

    public function setNumVolAller(?string $numVolAller): self
    {
        $this->numVolAller = $numVolAller;

        return $this;
    }

    public function getNumVolRetour(): ?string
    {
        return $this->numVolRetour;
    }

    public function setNumVolRetour(?string $numVolRetour): self
    {
        $this->numVolRetour = $numVolRetour;

        return $this;
    }

    public function getHeureVolAller(): ?\DateTimeInterface
    {
        return $this->heureVolAller;
    }

    public function setHeureVolAller(?\DateTimeInterface $heureVolAller): self
    {
        $this->heureVolAller = $heureVolAller;

        return $this;
    }

    public function getHeureVolRetour(): ?\DateTimeInterface
    {
        return $this->heureVolRetour;
    }

    public function setHeureVolRetour(?\DateTimeInterface $heureVolRetour): self
    {
        $this->heureVolRetour = $heureVolRetour;

        return $this;
    }
}
