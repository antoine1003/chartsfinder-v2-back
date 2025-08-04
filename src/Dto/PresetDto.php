<?php

namespace App\Dto;

// Assert
use Symfony\Component\Validator\Constraints as Assert;

class PresetDto implements \JsonSerializable
{
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Type("string")]
    #[Assert\Length(min: 1, max: 255)]
    private ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Type("array")]
    #[Assert\Count(min: 1)]
    #[Assert\All([
        new Assert\Type("integer"),
        new Assert\PositiveOrZero(),
    ])]
    private ?array $airports = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getAirports(): ?array
    {
        return $this->airports;
    }

    public function setAirports(?array $airports): void
    {
        $this->airports = $airports;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'airports' => $this->airports,
        ];
    }
}
