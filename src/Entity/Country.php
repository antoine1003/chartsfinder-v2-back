<?php
/**
 * @source https://ourairports.com/data/
 */
namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(length: 2)]
    private string $code;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 2)]
    private string $continent;

    #[ORM\Column(length: 255)]
    private string $wikipediaLink;

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

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getContinent(): string
    {
        return $this->continent;
    }

    public function setContinent(string $continent): void
    {
        $this->continent = $continent;
    }

    public function getWikipediaLink(): string
    {
        return $this->wikipediaLink;
    }

    public function setWikipediaLink(string $wikipediaLink): void
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
}
