<?php

namespace App\Entity;

use App\Repository\ChartRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: ChartRepository::class)]
#[ORM\Table(name: 'chart', indexes: [
    new ORM\Index(name: 'chart_name_idx', columns: ['name'])
])]
class Chart implements \JsonSerializable
{

    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(length: 255)]
    private ?string $airac = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'charts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Airport $airport = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $runway = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subType = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getAirac(): ?string
    {
        return $this->airac;
    }

    public function setAirac(string $airac): static
    {
        $this->airac = $airac;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'url' => $this->getUrl(),
            'airac' => $this->getAirac(),
            'type' => $this->getType(),
            'createdAt' => $this->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updatedAt' => $this->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
    }

    public function getAirport(): ?Airport
    {
        return $this->airport;
    }

    public function setAirport(?Airport $airport): static
    {
        $this->airport = $airport;

        return $this;
    }

    public function getSubType(): ?string
    {
        return $this->subType;
    }

    public function setSubType(?string $subType): static
    {
        $this->subType = $subType;

        return $this;
    }

    public function getRunway(): ?string
    {
        return $this->runway;
    }

    public function setRunway(?string $runway): static
    {
        $this->runway = $runway;
        return $this;
    }
}
