<?php

namespace App\Entity;

use App\Repository\UserNoticeDismissalRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: UserNoticeDismissalRepository::class)]
class UserNoticeDismissal
{
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userNoticeDismissals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userNoticeDismissals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserNotice $notice = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNotice(): ?UserNotice
    {
        return $this->notice;
    }

    public function setNotice(?UserNotice $notice): static
    {
        $this->notice = $notice;

        return $this;
    }
}
