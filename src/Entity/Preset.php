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
     * @var Collection<int, Airport>
     */
    #[ORM\ManyToMany(targetEntity: Airport::class, inversedBy: 'airports', cascade: ['persist'])]
    #[Groups(['preset:detail'])]
    private Collection $airports;

    #[ORM\ManyToOne(inversedBy: 'presets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    public function __construct()
    {
        $this->airports = new ArrayCollection();
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
     * @return Collection<int, Airport>
     */
    public function getAirports(): Collection
    {
        return $this->airports;
    }

    public function addAirport(Airport $airport): static
    {
        if (!$this->airports->contains($airport)) {
            $this->airports->add($airport);
        }

        return $this;
    }

    public function removeAirport(Airport $airport): static
    {
        $this->airports->removeElement($airport);

        return $this;
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
