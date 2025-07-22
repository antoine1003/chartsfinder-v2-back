<?php

namespace App\Entity;

use App\Repository\RunwayRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RunwayRepository::class)]
class Runway
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Airport::class)]
    private Airport $airport;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $lengthFt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $widthFt = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $surface = null;

    #[ORM\Column(type: 'boolean')]
    private bool $lighted;

    #[ORM\Column(type: 'boolean')]
    private bool $closed;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $leIdent = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $leLatitudeDeg = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $leLongitudeDeg = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $leElevationFt = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $leHeadingDegT = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $leDisplacedThresholdFt = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $heIdent = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $heLatitudeDeg = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $heLongitudeDeg = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $heElevationFt = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $heHeadingDegT = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $heDisplacedThresholdFt = null;


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

    public function isLighted(): bool
    {
        return $this->lighted;
    }

    public function setLighted(bool $lighted): void
    {
        $this->lighted = $lighted;
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function setClosed(bool $closed): void
    {
        $this->closed = $closed;
    }

    public function getLeIdent(): ?string
    {
        return $this->leIdent;
    }

    public function setLeIdent(?string $leIdent): void
    {
        $this->leIdent = $leIdent;
    }

    public function getLeLatitudeDeg(): ?float
    {
        return $this->leLatitudeDeg;
    }

    public function setLeLatitudeDeg(?float $leLatitudeDeg): void
    {
        $this->leLatitudeDeg = $leLatitudeDeg;
    }

    public function getLeLongitudeDeg(): ?float
    {
        return $this->leLongitudeDeg;
    }

    public function setLeLongitudeDeg(?float $leLongitudeDeg): void
    {
        $this->leLongitudeDeg = $leLongitudeDeg;
    }

    public function getLeElevationFt(): ?float
    {
        return $this->leElevationFt;
    }

    public function setLeElevationFt(?float $leElevationFt): void
    {
        $this->leElevationFt = $leElevationFt;
    }

    public function getLeHeadingDegT(): ?float
    {
        return $this->leHeadingDegT;
    }

    public function setLeHeadingDegT(?float $leHeadingDegT): void
    {
        $this->leHeadingDegT = $leHeadingDegT;
    }

    public function getLeDisplacedThresholdFt(): ?float
    {
        return $this->leDisplacedThresholdFt;
    }

    public function setLeDisplacedThresholdFt(?float $leDisplacedThresholdFt): void
    {
        $this->leDisplacedThresholdFt = $leDisplacedThresholdFt;
    }

    public function getHeIdent(): ?string
    {
        return $this->heIdent;
    }

    public function setHeIdent(?string $heIdent): void
    {
        $this->heIdent = $heIdent;
    }

    public function getHeLatitudeDeg(): ?float
    {
        return $this->heLatitudeDeg;
    }

    public function setHeLatitudeDeg(?float $heLatitudeDeg): void
    {
        $this->heLatitudeDeg = $heLatitudeDeg;
    }

    public function getHeLongitudeDeg(): ?float
    {
        return $this->heLongitudeDeg;
    }

    public function setHeLongitudeDeg(?float $heLongitudeDeg): void
    {
        $this->heLongitudeDeg = $heLongitudeDeg;
    }

    public function getHeElevationFt(): ?float
    {
        return $this->heElevationFt;
    }

    public function setHeElevationFt(?float $heElevationFt): void
    {
        $this->heElevationFt = $heElevationFt;
    }

    public function getHeHeadingDegT(): ?float
    {
        return $this->heHeadingDegT;
    }

    public function setHeHeadingDegT(?float $heHeadingDegT): void
    {
        $this->heHeadingDegT = $heHeadingDegT;
    }

    public function getHeDisplacedThresholdFt(): ?float
    {
        return $this->heDisplacedThresholdFt;
    }

    public function setHeDisplacedThresholdFt(?float $heDisplacedThresholdFt): void
    {
        $this->heDisplacedThresholdFt = $heDisplacedThresholdFt;
    }

    public function getAirport(): Airport
    {
        return $this->airport;
    }

    public function setAirport(Airport $airport): void
    {
        $this->airport = $airport;
    }
}
