<?php

namespace App\Entity;

use App\Repository\AirportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AirportRepository::class)]
#[ORM\Table(name: 'airport', indexes: [
    new ORM\Index(name: 'icao_code_idx', columns: ['icao_code']),
    new ORM\Index(name: 'iata_code_idx', columns: ['iata_code']),
])]
class Airport implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $type;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: 'float')]
    private float $latitudeDeg;

    #[ORM\Column(type: 'float')]
    private float $longitudeDeg;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $elevationFt = null;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    private Country $country;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $icaoCode = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $iataCode = null;

    /**
     * @var Collection<int, Chart>
     */
    #[ORM\OneToMany(targetEntity: Chart::class, mappedBy: 'airport', orphanRemoval: true)]
    private Collection $charts;

    /**
     * @var Collection<int, Runway>
     */
    #[ORM\OneToMany(targetEntity: Runway::class, mappedBy: 'airport', orphanRemoval: true)]
    private Collection $runways;

    public function __construct()
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
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

    public function getIataCode(): ?string
    {
        return $this->iataCode;
    }

    public function setIataCode(?string $iataCode): void
    {
        $this->iataCode = $iataCode;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function setCountry(Country $country): void
    {
        $this->country = $country;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'name' => $this->getName(),
            'latitudeDeg' => $this->getLatitudeDeg(),
            'longitudeDeg' => $this->getLongitudeDeg(),
            'elevationFt' => $this->getElevationFt(),
            'country' => $this->getCountry()->getName(), // Assuming Country entity has a getName() method
            'icaoCode' => $this->getIcaoCode(),
            'iataCode' => $this->getIataCode(),
        ];
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
}
