<?php

namespace App\Entity;

use App\Repository\ContractRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ContractRepository::class)
 */
class Contract
{
    // Statuts pour le contrat
    public const STATUS_UNSIGNED = 'unsigned';
    public const STATUS_CLIENT_SIGNED = 'client_signed';
    public const STATUS_ADMIN_SIGNED = 'admin_signed';
    public const STATUS_FULLY_SIGNED = 'fully_signed';
    public const STATUS_DECLINED = 'declined';

    // Statuts pour l'état des lieux départ (checkin)
    public const CHECKIN_STATUS_UNSIGNED = 'checkin_unsigned';
    public const CHECKIN_STATUS_CLIENT_SIGNED = 'checkin_client_signed';
    public const CHECKIN_STATUS_ADMIN_SIGNED = 'checkin_admin_signed';
    public const CHECKIN_STATUS_FULLY_SIGNED = 'checkin_fully_signed';

    // Statuts pour l'état des lieux retour (checkout)
    public const CHECKOUT_STATUS_UNSIGNED = 'checkout_unsigned';
    public const CHECKOUT_STATUS_CLIENT_SIGNED = 'checkout_client_signed';
    public const CHECKOUT_STATUS_ADMIN_SIGNED = 'checkout_admin_signed';
    public const CHECKOUT_STATUS_FULLY_SIGNED = 'checkout_fully_signed';

    public const VALID_STATUSES = [
        self::STATUS_UNSIGNED,
        self::STATUS_CLIENT_SIGNED,
        self::STATUS_ADMIN_SIGNED,
        self::STATUS_FULLY_SIGNED,
        self::STATUS_DECLINED
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"contract:read", "reservation:read"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Reservation::class, inversedBy="contracts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"contract:read"})
     * @Assert\NotNull
     */
    private $reservation;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"contract:read"})
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $contractHash;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"contract:read"})
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\Entity\Contract", "getValidStatuses"})
     */
    private $contractStatus = self::STATUS_UNSIGNED;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"contract:read"})
     */
    private $contractContent;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=ContractSignature::class, mappedBy="contract", cascade={"persist", "remove"})
     * @Groups({"contract:read"})
     */
    private $signatures;

    public function __construct()
    {
        $this->signatures = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

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

    public function getContractHash(): ?string
    {
        return $this->contractHash;
    }

    public function setContractHash(string $contractHash): self
    {
        $this->contractHash = $contractHash;

        return $this;
    }

    public function getContractStatus(): ?string
    {
        return $this->contractStatus;
    }

    public function setContractStatus(string $contractStatus): self
    {
        if (!in_array($contractStatus, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException("Invalid contract status: {$contractStatus}");
        }

        $this->contractStatus = $contractStatus;

        return $this;
    }

    public function getContractContent(): ?string
    {
        return $this->contractContent;
    }

    public function setContractContent(?string $contractContent): self
    {
        $this->contractContent = $contractContent;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection|ContractSignature[]
     */
    public function getSignatures(): Collection
    {
        return $this->signatures;
    }

    public function addSignature(ContractSignature $signature): self
    {
        if (!$this->signatures->contains($signature)) {
            $this->signatures[] = $signature;
            $signature->setContract($this);
        }

        return $this;
    }

    public function removeSignature(ContractSignature $signature): self
    {
        if ($this->signatures->removeElement($signature)) {
            // set the owning side to null (unless already changed)
            if ($signature->getContract() === $this) {
                $signature->setContract(null);
            }
        }

        return $this;
    }

    /**
     * Vérifie si le client a signé pour un type de document spécifique
     */
    public function isSignedByClient(string $documentType = ContractSignature::DOC_CONTRACT): bool
    {
        foreach ($this->signatures as $signature) {
            if (
                $signature->getSignatureType() === ContractSignature::TYPE_CLIENT
                && $signature->getDocumentType() === $documentType
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Vérifie si l'admin a signé pour un type de document spécifique
     */
    public function isSignedByAdmin(string $documentType = ContractSignature::DOC_CONTRACT): bool
    {
        foreach ($this->signatures as $signature) {
            if (
                $signature->getSignatureType() === ContractSignature::TYPE_ADMIN
                && $signature->getDocumentType() === $documentType
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Vérifie si un document est entièrement signé (client + admin)
     */
    public function isFullySigned(string $documentType = ContractSignature::DOC_CONTRACT): bool
    {
        return $this->isSignedByClient($documentType) && $this->isSignedByAdmin($documentType);
    }

    /**
     * Vérifie si la signature checkout est possible (le départ/contrat doit être signé)
     */
    public function canSignCheckout(): bool
    {
        return $this->isSignedByClient(ContractSignature::DOC_CONTRACT);
    }

    /**
     * Récupère le statut d'un type de document spécifique
     */
    public function getDocumentStatus(string $documentType = ContractSignature::DOC_CONTRACT): string
    {
        $hasClient = $this->isSignedByClient($documentType);
        $hasAdmin = $this->isSignedByAdmin($documentType);

        if ($hasClient && $hasAdmin) {
            switch ($documentType) {
                case ContractSignature::DOC_CONTRACT:
                    return self::STATUS_FULLY_SIGNED;
                case ContractSignature::DOC_CHECKIN:
                    return self::CHECKIN_STATUS_FULLY_SIGNED;
                case ContractSignature::DOC_CHECKOUT:
                    return self::CHECKOUT_STATUS_FULLY_SIGNED;
                default:
                    return 'fully_signed';
            }
        } elseif ($hasClient) {
            switch ($documentType) {
                case ContractSignature::DOC_CONTRACT:
                    return self::STATUS_CLIENT_SIGNED;
                case ContractSignature::DOC_CHECKIN:
                    return self::CHECKIN_STATUS_CLIENT_SIGNED;
                case ContractSignature::DOC_CHECKOUT:
                    return self::CHECKOUT_STATUS_CLIENT_SIGNED;
                default:
                    return 'client_signed';
            }
        } elseif ($hasAdmin) {
            switch ($documentType) {
                case ContractSignature::DOC_CONTRACT:
                    return self::STATUS_ADMIN_SIGNED;
                case ContractSignature::DOC_CHECKIN:
                    return self::CHECKIN_STATUS_ADMIN_SIGNED;
                case ContractSignature::DOC_CHECKOUT:
                    return self::CHECKOUT_STATUS_ADMIN_SIGNED;
                default:
                    return 'admin_signed';
            }
        }

        switch ($documentType) {
            case ContractSignature::DOC_CONTRACT:
                return self::STATUS_UNSIGNED;
            case ContractSignature::DOC_CHECKIN:
                return self::CHECKIN_STATUS_UNSIGNED;
            case ContractSignature::DOC_CHECKOUT:
                return self::CHECKOUT_STATUS_UNSIGNED;
            default:
                return 'unsigned';
        }
    }



    public static function getValidStatuses(): array
    {
        return self::VALID_STATUSES;
    }
}