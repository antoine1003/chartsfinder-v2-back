<?php

namespace App\Dto;

// Assert
use Symfony\Component\Validator\Constraints as Assert;

class SearchCriteriaDto
{
    #[Assert\NotBlank]
    private ?string $query = null;

    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Type("string")
    ])]
    private array $properties = [];


    public function getQuery(): string
    {
        return $this->query;
    }

    public function setQuery(string $query): void
    {
        $this->query = $query;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setProperties(array $properties): void
    {
        $this->properties = $properties;
    }
}
