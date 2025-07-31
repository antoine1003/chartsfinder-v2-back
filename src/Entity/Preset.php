<?php

namespace App\Entity;

use App\Dto\PresetDto;
use App\Repository\PresetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: PresetRepository::class)]
class Preset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['preset:list', 'preset:detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['preset:list', 'preset:detail'])]
    private ?string $name = null;

    /**
     * @var Collection<int, Chart>
     */
    #[ORM\ManyToMany(targetEntity: Chart::class, inversedBy: 'presets', cascade: ['persist'])]
    #[Groups(['preset:detail'])]
    private Collection $charts;

    #[ORM\ManyToOne(inversedBy: 'presets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
