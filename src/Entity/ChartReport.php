<?php

namespace App\Entity;

use App\Repository\ChartReportRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: ChartReportRepository::class)]
class ChartReport
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['report:detail'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'chartReports')]
    #[Groups(['report:detail'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Chart $chart = null;

    #[ORM\ManyToOne(inversedBy: 'chartReports')]
    #[Groups(['report:detail'])]
    private ?User $user = null;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['report:detail'])]
    private ?bool $resolved = null;

    #[Groups(['report:detail'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $createdAt;

    #[Groups(['report:detail'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChart(): ?Chart
    {
        return $this->chart;
    }

    public function setChart(?Chart $chart): static
    {
        $this->chart = $chart;

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

    public function isResolved(): ?bool
    {
        return $this->resolved;
    }

    public function setResolved(bool $resolved): static
    {
        $this->resolved = $resolved;

        return $this;
    }
}
