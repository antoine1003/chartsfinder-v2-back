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
        $this->logger->info('Sending email verification for user registration', [
            'user' => $event->getUser()->getEmail(),
        ]);
        $user = $event->getUser();

        $verificationUrl = $this->urlGenerator->generate(
            'app_verify_email',
            ['token' => $user->getEmailValidationToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        if (is_null($user->getGoogleId())) {
            $email = (new Email())
                ->from('noreply@chartsfinder.com')
                ->to($user->getEmail())
                ->addBcc("antoine.dautry@gmail.com")
                ->subject('Vérification de votre adresse e-mail')
                ->html(sprintf(
                    '<p>Hello 👋</p>
                 <p>Thanks for joining us ! Please click on the link bellow to validate your account :</p>
                 <p><a href="%s">%s</a></p>
                 <p>Chartsfinder V2</p>',
                    $verificationUrl,
                    $verificationUrl
                ));

            $this->mailer->send($email);
        } else {
            // Send email to administrator for Google account registration
            $email = (new Email())
                ->from('noreply@chartsfinder.com')
                ->to($user->getEmail())
                ->addBcc("antoine.dautry@gmail.com")
                ->subject('Nouvelle inscription avec un compte Google')
                ->html(sprintf(
                    '<p>Hello 👋</p>
                 <p>New user registered with a Google account.</p>
                 <p>User email: %s</p>
                 <p>Chartsfinder V2</p>',
                    $user->getEmail()
                ));

            $this->mailer->send($email);
        }

        $this->logger->info('Email verification sent', [
            'user' => $user->getEmail(),
            'verificationUrl' => $verificationUrl,
        ]);
    }
}
