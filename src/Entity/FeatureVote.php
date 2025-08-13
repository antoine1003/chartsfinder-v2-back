<?php

namespace App\Entity;

use App\Repository\FeatureVoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeatureVoteRepository::class)]
class FeatureVote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'featureVotes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Feature $feature = null;

    #[ORM\ManyToOne(inversedBy: 'featureVotes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 5)]
    private ?string $vote = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFeature(): ?Feature
    {
        return $this->feature;
    }

    public function setFeature(?Feature $feature): static
    {
        $this->feature = $feature;

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

    public function getVote(): ?string
    {
        return $this->vote;
    }

    public function setVote(string $vote): static
    {
        $this->vote = $vote;

        return $this;
    }
}
