<?php

namespace App\Entity;

use App\Repository\SourceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: SourceRepository::class)]
class Source
{
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['source:list', 'source:update'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['source:list', 'source:update'])]
    private ?string $countryCode = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['source:list', 'source:update'])]
    private ?string $url = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['source:list', 'source:update'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['source:list', 'source:update'])]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    #[Groups(['source:list', 'source:update'])]
    private ?string $urlName = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups(['source:list', 'source:update'])]
    private ?bool $ifr = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups(['source:list', 'source:update'])]
    private ?bool $vfr = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getUrlName(): ?string
    {
        return $this->urlName;
    }

    public function setUrlName(string $urlName): static
    {
        $this->urlName = $urlName;

        return $this;
    }

    public function isIfr(): ?bool
    {
        return $this->ifr;
    }

    public function setIfr(bool $ifr): static
    {
        $this->ifr = $ifr;

        return $this;
    }

    public function isVfr(): ?bool
    {
        return $this->vfr;
    }

    public function setVfr(bool $vfr): static
    {
        $this->vfr = $vfr;

        return $this;
    }
}
