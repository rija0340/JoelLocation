<?php

namespace App\Entity;

use App\Repository\AvisRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AvisRepository::class)
 */
class Avis
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Reservation::class, inversedBy="avis")
     * @ORM\JoinColumn(nullable=false)
     */
    private $reservation;

    /**
     * @ORM\Column(type="integer")
     */
    private $global;

    /**
     * @ORM\Column(type="integer")
     */
    private $ponctualite;

    /**
     * @ORM\Column(type="integer")
     */
    private $accueil;

    /**
     * @ORM\Column(type="integer")
     */
    private $service;

    /**
     * @ORM\Column(type="text")
     */
    private $commentaire;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        $this->reservation = $reservation;

        return $this;
    }

    public function getGlobal(): ?int
    {
        return $this->global;
    }

    public function setGlobal(int $global): self
    {
        $this->global = $global;

        return $this;
    }

    public function getPonctualite(): ?int
    {
        return $this->ponctualite;
    }

    public function setPonctualite(int $ponctualite): self
    {
        $this->ponctualite = $ponctualite;

        return $this;
    }

    public function getAccueil(): ?int
    {
        return $this->accueil;
    }

    public function setAccueil(int $accueil): self
    {
        $this->accueil = $accueil;

        return $this;
    }

    public function getService(): ?int
    {
        return $this->service;
    }

    public function setService(int $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }
}
