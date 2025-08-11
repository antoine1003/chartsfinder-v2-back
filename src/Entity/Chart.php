<?php

namespace App\Entity;

use App\Repository\ChartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ChartRepository::class)]
#[ORM\Table(name: 'chart', indexes: [
    new ORM\Index(name: 'chart_name_idx', columns: ['name'])
])]
#[ORM\UniqueConstraint(name: 'chart_airport_name_unique', columns: ['airport_id', 'name'])]
class Chart
{

    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['chart:list', 'chart:detail', 'preset:detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['chart:list', 'chart:detail', 'preset:detail'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['chart:list', 'chart:detail', 'preset:detail'])]
    private ?string $url = null;

    #[ORM\Column(length: 255)]
    #[Groups(['chart:list', 'chart:detail', 'preset:detail'])]
    private ?string $airac = null;

    #[ORM\Column(length: 255)]
    #[Groups(['chart:list', 'chart:detail', 'preset:detail'])]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'charts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Airport $airport = null;


    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['chart:list', 'chart:detail', 'preset:detail'])]
    private ?string $subType = null;

    /**
     * @var Collection<int, Runway>
     */
    #[ORM\ManyToMany(targetEntity: Runway::class, inversedBy: 'charts')]
    #[Groups(['chart:detail', 'preset:detail'])]
    private Collection $runways;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups(['chart:detail', 'preset:detail'])]
    private ?bool $needProxy = true;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->presets = new ArrayCollection();
        $this->runways = new ArrayCollection();
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


    /**
     * @return Collection<int, Runway>
     */
    public function getRunways(): Collection
    {
        return $this->runways;
    }

    public function addRunway(Runway $runway): static
    {
        if (!$this->runways->contains($runway)) {
            $this->runways->add($runway);
        }

        return $this;
    }

    public function removeRunway(Runway $runway): static
    {
        $this->runways->removeElement($runway);

        return $this;
    }

    public function isNeedProxy(): ?bool
    {
        return $this->needProxy;
    }

    public function setNeedProxy(bool $needProxy): static
    {
        $this->needProxy = $needProxy;

        return $this;
    }
}
