<?php
namespace App\Service\Auth;


use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class PasswordResetMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ParameterBagInterface $parameterBag
    ) {}

    public function send(string $toEmail, string $token): void
    {

        $frontendUrl = $this->parameterBag->get('frontendUrl');
        $link = rtrim($frontendUrl, '/').'/reset-password/confirm?token='.urlencode($token);

        $email = (new Email())
            ->from('noreply@chartsfinder.com')
            ->to($toEmail)
            ->subject('Reset your password')
            ->html("
          <p>Hello,</p>
          <p>We received a request to reset your password.</p>
          <p><a href=\"$link\">Click here to set a new password</a></p>
          <p>If you didn't request this, you can ignore this email.</p>
        ");

        $this->mailer->send($email);
    }
}
