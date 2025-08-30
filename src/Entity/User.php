<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['report:detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['report:detail'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    /**
     * @var Collection<int, Preset>
     */
    #[ORM\OneToMany(targetEntity: Preset::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $presets;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isEmailValidated = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailValidationToken = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $googleId = null;

    /**
     * @var Collection<int, FeatureVote>
     */
    #[ORM\OneToMany(targetEntity: FeatureVote::class, mappedBy: 'user')]
    private Collection $featureVotes;

    /**
     * @var Collection<int, Feature>
     */
    #[ORM\OneToMany(targetEntity: Feature::class, mappedBy: 'createdBy')]
    private Collection $features;


    /**
     * @var Collection<int, PasswordResetToken>
     */
    #[ORM\OneToMany(targetEntity: PasswordResetToken::class, mappedBy: 'user')]
    private Collection $passwordResetTokens;

    /**
     * @var Collection<int, ChartReport>
     */
    #[ORM\OneToMany(targetEntity: ChartReport::class, mappedBy: 'user')]
    private Collection $chartReports;

    /**
     * @var Collection<int, UserNoticeDismissal>
     */
    #[ORM\OneToMany(targetEntity: UserNoticeDismissal::class, mappedBy: 'user', cascade: ['persist'],)]
    private Collection $userNoticeDismissals;

    public function __construct()
    {
        $this->presets = new ArrayCollection();
        $this->featureVotes = new ArrayCollection();
        $this->features = new ArrayCollection();
        $this->passwordResetTokens = new ArrayCollection();
        $this->chartReports = new ArrayCollection();
        $this->userNoticeDismissals = new ArrayCollection();
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles(), true);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    /**
     * @return Collection<int, Preset>
     */
    public function getPresets(): Collection
    {
        return $this->presets;
    }

    public function addPreset(Preset $preset): static
    {
        if (!$this->presets->contains($preset)) {
            $this->presets->add($preset);
            $preset->setUser($this);
        }

        return $this;
    }

    public function removePreset(Preset $preset): static
    {
        if ($this->presets->removeElement($preset)) {
            // set the owning side to null (unless already changed)
            if ($preset->getUser() === $this) {
                $preset->setUser(null);
            }
        }

        return $this;
    }

    public function isEmailValidated(): ?bool
    {
        return $this->isEmailValidated;
    }

    public function setIsEmailValidated(bool $isEmailValidated): static
    {
        $this->isEmailValidated = $isEmailValidated;

        return $this;
    }

    public function getEmailValidationToken(): ?string
    {
        return $this->emailValidationToken;
    }

    public function setEmailValidationToken(?string $emailValidationToken): static
    {
        $this->emailValidationToken = $emailValidationToken;

        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): static
    {
        $this->googleId = $googleId;

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
            $featureVote->setUser($this);
        }

        return $this;
    }

    public function removeFeatureVote(FeatureVote $featureVote): static
    {
        if ($this->featureVotes->removeElement($featureVote)) {
            // set the owning side to null (unless already changed)
            if ($featureVote->getUser() === $this) {
                $featureVote->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Feature>
     */
    public function getFeatures(): Collection
    {
        return $this->features;
    }

    public function addFeature(Feature $feature): static
    {
        if (!$this->features->contains($feature)) {
            $this->features->add($feature);
            $feature->setCreatedBy($this);
        }

        return $this;
    }

    public function removeFeature(Feature $feature): static
    {
        if ($this->features->removeElement($feature)) {
            // set the owning side to null (unless already changed)
            if ($feature->getCreatedBy() === $this) {
                $feature->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PasswordResetToken>
     */
    public function getPasswordResetTokens(): Collection
    {
        return $this->passwordResetTokens;
    }

    public function addPasswordResetToken(PasswordResetToken $passwordResetToken): static
    {
        if (!$this->passwordResetTokens->contains($passwordResetToken)) {
            $this->passwordResetTokens->add($passwordResetToken);
            $passwordResetToken->setUser($this);
        }

        return $this;
    }

    public function removePasswordResetToken(PasswordResetToken $passwordResetToken): static
    {
        if ($this->passwordResetTokens->removeElement($passwordResetToken)) {
            // set the owning side to null (unless already changed)
            if ($passwordResetToken->getUser() === $this) {
                $passwordResetToken->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ChartReport>
     */
    public function getChartReports(): Collection
    {
        return $this->chartReports;
    }

    public function addChartReport(ChartReport $chartReport): static
    {
        if (!$this->chartReports->contains($chartReport)) {
            $this->chartReports->add($chartReport);
            $chartReport->setUser($this);
        }

        return $this;
    }

    public function removeChartReport(ChartReport $chartReport): static
    {
        if ($this->chartReports->removeElement($chartReport)) {
            // set the owning side to null (unless already changed)
            if ($chartReport->getUser() === $this) {
                $chartReport->setUser(null);
            }
        }

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
            $userNoticeDismissal->setUser($this);
        }

        return $this;
    }

    public function removeUserNoticeDismissal(UserNoticeDismissal $userNoticeDismissal): static
    {
        if ($this->userNoticeDismissals->removeElement($userNoticeDismissal)) {
            // set the owning side to null (unless already changed)
            if ($userNoticeDismissal->getUser() === $this) {
                $userNoticeDismissal->setUser(null);
            }
        }

        return $this;
    }

    public function dismissNotice(UserNotice $notice): void
    {
        foreach ($this->userNoticeDismissals as $dismissal) {
            if ($dismissal->getNotice() === $notice) {
                // Notice already dismissed
                return;
            }
        }

        $dismissal = new UserNoticeDismissal();
        $dismissal->setUser($this);
        $dismissal->setNotice($notice);
        $this->addUserNoticeDismissal($dismissal);
        return;
    }
}
