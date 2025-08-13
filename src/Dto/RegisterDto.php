<?php

namespace App\Dto;

// Assert
use Symfony\Component\Validator\Constraints as Assert;

class RegisterDto
{
    #[Assert\NotBlank]
    #[Assert\Type("string")]
    #[Assert\Length(min: 1, max: 255)]
    #[Assert\Email]
    private ?string $email = null;

    #[Assert\NotBlank]
    #[Assert\Type("string")]
    #[Assert\Length(min: 1, max: 255)]
    private ?string $displayName= null;

    #[Assert\NotBlank]
    #[Assert\Type("string")]
    #[Assert\Length(max: 255)]
    private ?string $password = null;

    #[Assert\NotBlank]
    #[Assert\Type("string")]
    private ?string $captchaToken = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getCaptchaToken(): ?string
    {
        return $this->captchaToken;
    }

    public function setCaptchaToken(?string $captchaToken): void
    {
        $this->captchaToken = $captchaToken;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): void
    {
        $this->displayName = $displayName;
    }
}
