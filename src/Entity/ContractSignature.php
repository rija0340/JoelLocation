<?php

namespace App\Entity;

use App\Repository\ContractSignatureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ContractSignatureRepository::class)
 */
class ContractSignature
{
    public const TYPE_CLIENT = 'client';
    public const TYPE_ADMIN = 'admin';

    public const VALID_TYPES = [
        self::TYPE_CLIENT,
        self::TYPE_ADMIN
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"contract:read", "signature:read"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Contract::class, inversedBy="signatures")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"signature:read"})
     * @Assert\NotNull
     */
    private $contract;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"contract:read", "signature:read"})
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\Entity\ContractSignature", "getValidTypes"})
     */
    private $signatureType;

    /**
     * @ORM\Column(type="text")
     * @Groups({"signature:read"})
     * @Assert\NotBlank
     */
    private $signatureData;

    /**
     * @ORM\Column(type="text")
     * @Groups({"signature:read"})
     * @Assert\NotBlank
     */
    private $publicKeyData;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"contract:read", "signature:read"})
     * @Assert\NotNull
     */
    private $signedAt;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     * @Assert\Length(max=45)
     */
    private $ipAddress;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $userAgent;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"contract:read", "signature:read"})
     */
    private $signatureValid = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"signature:read"})
     */
    private $timestampToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"contract:read", "signature:read"})
     */
    private $timestampVerifiedAt;

    public function __construct()
    {
        $this->signedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    public function setContract(?Contract $contract): self
    {
        $this->contract = $contract;

        return $this;
    }

    public function getSignatureType(): ?string
    {
        return $this->signatureType;
    }

    public function setSignatureType(string $signatureType): self
    {
        if (!in_array($signatureType, self::VALID_TYPES)) {
            throw new \InvalidArgumentException("Invalid signature type: {$signatureType}");
        }

        $this->signatureType = $signatureType;

        return $this;
    }

    public function getSignatureData(): ?string
    {
        return $this->signatureData;
    }

    public function setSignatureData(string $signatureData): self
    {
        $this->signatureData = $signatureData;

        return $this;
    }

    public function getPublicKeyData(): ?string
    {
        return $this->publicKeyData;
    }

    public function setPublicKeyData(string $publicKeyData): self
    {
        $this->publicKeyData = $publicKeyData;

        return $this;
    }

    public function getSignedAt(): ?\DateTimeInterface
    {
        return $this->signedAt;
    }

    public function setSignedAt(\DateTimeInterface $signedAt): self
    {
        $this->signedAt = $signedAt;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getSignatureValid(): ?bool
    {
        return $this->signatureValid;
    }

    public function setSignatureValid(bool $signatureValid): self
    {
        $this->signatureValid = $signatureValid;

        return $this;
    }

    public function getTimestampToken(): ?string
    {
        return $this->timestampToken;
    }

    public function setTimestampToken(?string $timestampToken): self
    {
        $this->timestampToken = $timestampToken;

        return $this;
    }

    public function getTimestampVerifiedAt(): ?\DateTimeInterface
    {
        return $this->timestampVerifiedAt;
    }

    public function setTimestampVerifiedAt(?\DateTimeInterface $timestampVerifiedAt): self
    {
        $this->timestampVerifiedAt = $timestampVerifiedAt;

        return $this;
    }

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"signature:read"})
     */
    private $signatureImage;

    public function getSignatureImage(): ?string
    {
        return $this->signatureImage;
    }

    public function setSignatureImage(?string $signatureImage): self
    {
        $this->signatureImage = $signatureImage;

        return $this;
    }

    public static function getValidTypes(): array
    {
        return self::VALID_TYPES;
    }
}