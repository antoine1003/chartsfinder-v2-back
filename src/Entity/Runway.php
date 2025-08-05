<?php

namespace App\Entity;

use App\Repository\RunwayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RunwayRepository::class)]
class Runway
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['preset:detail', 'airport:detail', 'runway:detail', 'airport:list'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Airport::class)]
    private Airport $airport;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['preset:detail', 'airport:detail', 'airport:list'])]
    private ?int $lengthFt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['preset:detail', 'airport:detail', 'airport:list'])]
    private ?int $widthFt = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['preset:detail', 'airport:detail', 'airport:list'])]
    private ?string $surface = null;


    #[ORM\Column(length: 3, nullable: true)]
    #[Groups(['preset:detail', 'airport:detail', 'airport:list'])]
    private ?int $heading = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['preset:detail', 'airport:detail', 'airport:list'])]
    private ?bool $ilsAvailable = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['preset:detail', 'airport:detail', 'airport:list'])]
    private ?string $ilsFrequency = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['preset:detail', 'airport:detail', 'airport:list'])]
    private ?int $ilsQdm = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['preset:detail', 'airport:detail', 'airport:list'])]
    private ?string $ident = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['runway:detail'])]
    private ?float $lat = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['runway:detail'])]
    private ?float $lon = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['runway:detail', 'airport:list'])]
    private ?float $elevationFt = null;

    /**
     * @var Collection<int, Chart>
     */
    #[ORM\ManyToMany(targetEntity: Chart::class, mappedBy: 'runways')]
    private Collection $charts;

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

    public function getLengthFt(): ?int
    {
        return $this->lengthFt;
    }

    public function setLengthFt(?int $lengthFt): void
    {
        $this->lengthFt = $lengthFt;
    }

    public function getWidthFt(): ?int
    {
        return $this->widthFt;
    }

    public function setWidthFt(?int $widthFt): void
    {
        $this->widthFt = $widthFt;
    }

    public function getSurface(): ?string
    {
        return $this->surface;
    }

    public function setSurface(?string $surface): void
    {
        $this->surface = $surface;
    }



    public function getIdent(): ?string
    {
        return $this->ident;
    }

    public function setIdent(?string $ident): void
    {
        $this->ident = $ident;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(?float $lat): void
    {
        $this->lat = $lat;
    }

    public function getLon(): ?float
    {
        return $this->lon;
    }

    public function setLon(?float $lon): void
    {
        $this->lon = $lon;
    }

    public function getElevationFt(): ?float
    {
        return $this->elevationFt;
    }

    public function setElevationFt(?float $elevationFt): void
    {
        $this->elevationFt = $elevationFt;
    }

    public function getAirport(): Airport
    {
        return $this->airport;
    }

    public function setAirport(Airport $airport): void
    {
        $this->airport = $airport;
    }


    public function getHeading(): ?int
    {
        return $this->heading;
    }

    public function setHeading(?int $heading): void
    {
        $this->heading = $heading;
    }

    public function getIlsAvailable(): ?bool
    {
        return $this->ilsAvailable;
    }

    public function setIlsAvailable(?bool $ilsAvailable): void
    {
        $this->ilsAvailable = $ilsAvailable;
    }

    public function getIlsFrequency(): ?string
    {
        return $this->ilsFrequency;
    }

    public function setIlsFrequency(?string $ilsFrequency): void
    {
        $this->ilsFrequency = $ilsFrequency;
    }

    public function getIlsQdm(): ?int
    {
        return $this->ilsQdm;
    }

    public function setIlsQdm(?int $ilsQdm): void
    {
        $this->ilsQdm = $ilsQdm;
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
            $chart->addRunway($this);
        }

        return $this;
    }

    public function removeChart(Chart $chart): static
    {
        if ($this->charts->removeElement($chart)) {
            $chart->removeRunway($this);
        }

        return $this;
    }
}
