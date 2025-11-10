<?php

namespace App\EventListener;

use App\Event\UserRegisteredEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener(event: UserRegisteredEvent::NAME, method: 'onUserRegistered')]
readonly class SendEmailVerificationListener
{
    public function __construct(
        private MailerInterface       $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface       $logger
    ) {}

    /**
     * @throws TransportExceptionInterface
     */
    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        $user = $event->getUser();
        $success = true;
        $verificationUrl = null;
        if (is_null($user->getGoogleId())) {
            $verificationUrl = $this->urlGenerator->generate(
                'app_verify_email',
                ['token' => $user->getEmailValidationToken()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $email = (new Email())
                ->from('noreply@chartsfinder.com')
                ->to($user->getEmail())
                ->addBcc("antoine.dautry@gmail.com")
                ->subject('Email verification for Chartsfinder V2')
                ->html(sprintf(
                    '<p>Hello 👋</p>
                 <p>Thanks for joining us ! Please click on the link bellow to validate your account :</p>
                 <p><a href="%s">%s</a></p>
                 <p>Chartsfinder V2</p>',
                    $verificationUrl,
                    $verificationUrl
                ));
        } else {
            // Send email to administrator for Google account registration
            $email = (new Email())
                ->from('noreply@chartsfinder.com')
                ->to("antoine.dautry@gmail.com")
                ->subject('Nouvelle inscription avec un compte Google')
                ->html(sprintf(
                    '<p>Hello 👋</p>
                 <p>New user registered with a Google account.</p>
                 <p>User email: %s</p>
                 <p>Chartsfinder V2</p>',
                    $user->getEmail()
                ));
        }

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to send email verification', [
                'user' => $user->getEmail(),
                'error' => $e->getMessage(),
            ]);
            $success = false;
        }

        if (!$success) {
            return;
        }

        if (is_null($user->getGoogleId())) {
            $this->logger->info('Email verification sent', [
                'user' => $user->getEmail(),
                'verificationUrl' => $verificationUrl,
            ]);
        }
    }
}
