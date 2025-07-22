<?php

namespace App\Entity;

use App\Repository\AirportRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AirportRepository::class)]
#[ORM\Table(name: 'airport', indexes: [
    new ORM\Index(name: 'icao_code_idx', columns: ['icao_code']),
    new ORM\Index(name: 'iata_code_idx', columns: ['iata_code']),
])]
class Airport implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(length: 10)]
    private string $ident;

    #[ORM\Column(length: 50)]
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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $municipality = null;

    #[ORM\Column(type: 'boolean')]
    private bool $scheduledService;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $icaoCode = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $iataCode = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $gpsCode = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $localCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $homeLink = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $wikipediaLink = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $keywords = null;



    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getIdent(): string
    {
        return $this->ident;
    }

    public function setIdent(string $ident): void
    {
        $this->ident = $ident;
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

    public function setName(string $name): void
    {
        $this->name = $name;
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


    public function getMunicipality(): ?string
    {
        return $this->municipality;
    }

    public function setMunicipality(?string $municipality): void
    {
        $this->municipality = $municipality;
    }

    public function isScheduledService(): bool
    {
        return $this->scheduledService;
    }

    public function setScheduledService(bool $scheduledService): void
    {
        $this->scheduledService = $scheduledService;
    }

    public function getIcaoCode(): ?string
    {
        return $this->icaoCode;
    }

    public function setIcaoCode(?string $icaoCode): void
    {
        $this->icaoCode = $icaoCode;
    }

    public function getIataCode(): ?string
    {
        return $this->iataCode;
    }

    public function setIataCode(?string $iataCode): void
    {
        $this->iataCode = $iataCode;
    }

    public function getGpsCode(): ?string
    {
        return $this->gpsCode;
    }

    public function setGpsCode(?string $gpsCode): void
    {
        $this->gpsCode = $gpsCode;
    }

    public function getLocalCode(): ?string
    {
        return $this->localCode;
    }

    public function setLocalCode(?string $localCode): void
    {
        $this->localCode = $localCode;
    }

    public function getHomeLink(): ?string
    {
        return $this->homeLink;
    }

    public function setHomeLink(?string $homeLink): void
    {
        $this->homeLink = $homeLink;
    }

    public function getWikipediaLink(): ?string
    {
        return $this->wikipediaLink;
    }

    public function setWikipediaLink(?string $wikipediaLink): void
    {
        $this->wikipediaLink = $wikipediaLink;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(?string $keywords): void
    {
        $this->keywords = $keywords;
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
            'ident' => $this->getIdent(),
            'type' => $this->getType(),
            'name' => $this->getName(),
            'latitudeDeg' => $this->getLatitudeDeg(),
            'longitudeDeg' => $this->getLongitudeDeg(),
            'elevationFt' => $this->getElevationFt(),
            'country' => $this->getCountry()->getName(), // Assuming Country entity has a getName() method
            'municipality' => $this->getMunicipality(),
            'scheduledService' => $this->isScheduledService(),
            'icaoCode' => $this->getIcaoCode(),
            'iataCode' => $this->getIataCode(),
            'gpsCode' => $this->getGpsCode(),
            'localCode' => $this->getLocalCode(),
            'homeLink' => $this->getHomeLink(),
            'wikipediaLink' => $this->getWikipediaLink(),
            'keywords' => $this->getKeywords()
        ];
    }
}
