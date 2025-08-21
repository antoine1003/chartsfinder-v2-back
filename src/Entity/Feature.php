<?php

namespace App\Entity;

use App\Entity\Enum\FeatureStatusEnum;
use App\Repository\FeatureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FeatureRepository::class)]
class Feature
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['feature:create', 'feature:update'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['feature:create', 'feature:update'])]
    #[Assert\NotBlank(message: "titleRequired")]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['feature:create', 'feature:update'])]
    #[Assert\NotBlank(message: "descriptionRequired")]
    private ?string $description = null;

    /**
     * @var Collection<int, FeatureVote>
     */
    #[ORM\OneToMany(targetEntity: FeatureVote::class, mappedBy: 'feature')]
    private Collection $featureVotes;

    #[ORM\Column(length: 255)]
    private ?string $status = FeatureStatusEnum::PENDING;

    #[ORM\ManyToOne(inversedBy: 'features')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\Column(length: 20)]
    #[Groups(['feature:create', 'feature:update'])]
    private ?string $tag = null;

    public function __construct()
    {
        $this->featureVotes = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, FeatureVote>
     */
    public function getFeatureVotes(): Collection
    {
        return $this->featureVotes;
    }

    public function addFeatureVote(FeatureVote $featureVote): static
    {
        if (!$this->featureVotes->contains($featureVote)) {
            $this->featureVotes->add($featureVote);
            $featureVote->setFeature($this);
        }

        return $this;
    }

    public function removeFeatureVote(FeatureVote $featureVote): static
    {
        if ($this->featureVotes->removeElement($featureVote)) {
            // set the owning side to null (unless already changed)
            if ($featureVote->getFeature() === $this) {
                $featureVote->setFeature(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }
}
