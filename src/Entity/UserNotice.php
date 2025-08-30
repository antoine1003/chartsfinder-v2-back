<?php

namespace App\Entity;

use App\Repository\UserNoticeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: UserNoticeRepository::class)]
class UserNotice
{
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['notice:list', 'notice:detail', 'notice:create', 'notice:update'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['notice:list', 'notice:detail', 'notice:create', 'notice:update'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['notice:list', 'notice:detail', 'notice:create', 'notice:update'])]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    #[Groups(['notice:list', 'notice:detail', 'notice:create', 'notice:update'])]
    private ?string $severity = null;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['notice:list', 'notice:detail', 'notice:create', 'notice:update'])]
    private ?bool $isActive = true;

    #[Groups(['notice:list', 'notice:detail'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $createdAt;

    #[Groups(['notice:list', 'notice:detail'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $updatedAt;

    /**
     * @var Collection<int, UserNoticeDismissal>
     */
    #[ORM\OneToMany(targetEntity: UserNoticeDismissal::class, mappedBy: 'notice', cascade: ['persist'], orphanRemoval: true)]
    private Collection $userNoticeDismissals;

    public function __construct()
    {
        $this->userNoticeDismissals = new ArrayCollection();
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getSeverity(): ?string
    {
        return $this->severity;
    }

    public function setSeverity(string $severity): static
    {
        $this->severity = $severity;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, UserNoticeDismissal>
     */
    public function getUserNoticeDismissals(): Collection
    {
        return $this->userNoticeDismissals;
    }

    public function addUserNoticeDismissal(UserNoticeDismissal $userNoticeDismissal): static
    {
        if (!$this->userNoticeDismissals->contains($userNoticeDismissal)) {
            $this->userNoticeDismissals->add($userNoticeDismissal);
            $userNoticeDismissal->setNotice($this);
        }

        return $this;
    }

    public function removeUserNoticeDismissal(UserNoticeDismissal $userNoticeDismissal): static
    {
        if ($this->userNoticeDismissals->removeElement($userNoticeDismissal)) {
            // set the owning side to null (unless already changed)
            if ($userNoticeDismissal->getNotice() === $this) {
                $userNoticeDismissal->setNotice(null);
            }
        }

        return $this;
    }
}
