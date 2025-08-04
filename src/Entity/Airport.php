<?php

namespace App\Entity;

use App\Repository\AirportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AirportRepository::class)]
#[ORM\Table(name: 'airport', indexes: [
    new ORM\Index(name: 'icao_code_idx', columns: ['icao_code'])
])]
class Airport
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['preset:detail', 'airport:detail', 'airport:list'])]
    private int $id;

    #[ORM\Column(length: 255)]
    #[Groups(['preset:detail', 'airport:detail', 'airport:list'])]
    private string $name;

    #[ORM\Column(type: 'float')]
    private float $latitudeDeg;

    #[ORM\Column(type: 'float')]
    private float $longitudeDeg;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $elevationFt = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['preset:detail', 'airport:detail', 'airport:list'])]
    private ?string $icaoCode = null;

    /**
     * @var Collection<int, Chart>
     */
    #[ORM\OneToMany(targetEntity: Chart::class, mappedBy: 'airport', orphanRemoval: true)]
    #[Groups(['preset:detail', 'airport:detail'])]
    private Collection $charts;

    /**
     * @var Collection<int, Runway>
     */
    #[ORM\OneToMany(targetEntity: Runway::class, mappedBy: 'airport', orphanRemoval: true)]
    #[Groups(['preset:detail', 'airport:detail'])]
    private Collection $runways;


    /**
     * @var Collection<int, Preset>
     */
    #[ORM\ManyToMany(targetEntity: Preset::class, mappedBy: 'charts', cascade: ['persist'])]
    private Collection $presets;

    public function __construct()
    {
        $this->charts = new ArrayCollection();
        $this->presets = new ArrayCollection();
    }

    public function __clone(): void
    {
        $this->charts = new ArrayCollection();
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getLatitudeDeg(): float
    {
        return $this->latitudeDeg;
    }

    public function setLatitudeDeg(float $latitudeDeg): void
    {
        $this->latitudeDeg = $latitudeDeg;
    }

    public function getLongitudeDeg(): float
    {
        return $this->longitudeDeg;
    }

    public function setLongitudeDeg(float $longitudeDeg): void
    {
        $this->longitudeDeg = $longitudeDeg;
    }

    public function getElevationFt(): ?float
    {
        return $this->elevationFt;
    }

    public function setElevationFt(?float $elevationFt): void
    {
        $this->elevationFt = $elevationFt;
    }


    public function getIcaoCode(): ?string
    {
        return $this->icaoCode;
    }


    public function setIcaoCode(?string $icaoCode): static
    {
        $this->icaoCode = $icaoCode;
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
            $chart->setAirport($this);
        }

        return $this;
    }

    public function removeChart(Chart $chart): static
    {
        if ($this->charts->removeElement($chart)) {
            // set the owning side to null (unless already changed)
            if ($chart->getAirport() === $this) {
                $chart->setAirport(null);
            }
        }

        return $this;
    }

    public function getRunways(): Collection
    {
        return $this->runways;
    }

    public function setRunways(Collection $runways): void
    {
        $this->runways = $runways;
    }

    /**
     * @return Collection<int, Preset>
     */
    public function getPresets(): Collection
    {
        return $this->presets;
    }

    public function addPreset(Preset $preset): static
    {
        if (!$this->presets->contains($preset)) {
            $this->presets->add($preset);
            $preset->addAirport($this);
        }

        return $this;
    }

    public function removePreset(Preset $preset): static
    {
        if ($this->presets->removeElement($preset)) {
            $preset->removeAirport($this);
        }

        return $this;
    }

}
