<?php

namespace App\Entity;

use App\Repository\TocTrackerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TocTrackerRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_country_code', columns: ['country_code'])]
class TocTracker
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 4)]
    private ?string $countryCode = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $tocUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $lastError = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $errorAt = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $airac = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): static
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getTocUrl(): ?string
    {
        return $this->tocUrl;
    }

    public function setTocUrl(string $tocUrl): static
    {
        $this->tocUrl = $tocUrl;

        return $this;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function setLastError(?string $lastError): static
    {
        $this->lastError = $lastError;

        return $this;
    }

    public function getErrorAt(): ?\DateTimeImmutable
    {
        return $this->errorAt;
    }

    public function setErrorAt(?\DateTimeImmutable $errorAt): static
    {
        $this->errorAt = $errorAt;

        return $this;
    }

    public function getAirac(): ?string
    {
        return $this->airac;
    }

    public function setAirac(?string $airac): static
    {
        $this->airac = $airac;

        return $this;
    }
}
