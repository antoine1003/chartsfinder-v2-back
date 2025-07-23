<?php

namespace App\Entity;

use App\Dto\PresetDto;
use App\Repository\PresetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PresetRepository::class)]
class Preset implements  \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Chart>
     */
    #[ORM\ManyToMany(targetEntity: Chart::class, inversedBy: 'presets', cascade: ['persist'])]
    private Collection $charts;

    public function __construct()
    {
        $this->charts = new ArrayCollection();
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

    /**
     * @return Collection<int, Chart>
     */
    public function getCharts(): Collection
    {
        return $this->charts;
    }

    public function addChart(Chart $chart): static
    {
        if (!$this->charts->contains($chart)) {
            $this->charts->add($chart);
        }

        return $this;
    }

    public function removeChart(Chart $chart): static
    {
        $this->charts->removeElement($chart);

        return $this;
    }

    public function toDto(): PresetDto
    {
        $presetDto = new PresetDto();
        $presetDto->setId($this->getId());
        $presetDto->setName($this->getName());
        $presetDto->setCharts($this->getCharts()->map(fn(Chart $chart) => $chart->getId())->toArray());

        return $presetDto;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'charts' => $this->getCharts()->map(function(Chart $chart) {
                    return [
                        'id' => $chart->getId(),
                        'name' => $chart->getName(),
                        'type' => $chart->getType(),
                    ];
            })->toArray()
        ];
    }
}
