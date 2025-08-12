<?php

namespace App\Service;

use App\Dto\PresetDto;
use App\Entity\Airport;
use App\Entity\Preset;
use App\Entity\User;
use App\Repository\AirportRepository;
use App\Repository\PresetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * @extends AbstractRestService<AirportRepository>
 */
class ContactService
{
    public function __construct(
        private readonly MailerInterface $mailer,
    )
    {
    }

    public function sendContactEmail(string $name, string $email, string $message): void
    {
        $emailMessage = (new Email())
            ->from('noreply@chartsfinder.com')
            ->to('antoine.dautry@gmail.com')
            ->replyTo($email)
            ->subject('Contact Form Submission')
            ->text("Name: $name\nEmail: $email\nMessage: $message");
        $this->mailer->send($emailMessage);
    }
}
