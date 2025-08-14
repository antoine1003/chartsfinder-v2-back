<?php

namespace App\EventListener;

use App\Event\NewFeatureEvent;
use App\Event\UserRegisteredEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener(event: NewFeatureEvent::NAME, method: 'onNewFeature')]
readonly class NewFeatureListener
{
    public function __construct(
        private MailerInterface       $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface       $logger
    ) {}

    /**
     * @throws TransportExceptionInterface
     */
    public function onNewFeature(NewFeatureEvent $event): void
    {
        $feature = $event->getFeature();

    // Send email to administrator for Google account registration
    $email = (new Email())
        ->from('noreply@chartsfinder.com')
        ->to("antoine.dautry@gmail.com")
        ->subject('[CF v2] New Feature Notification')
        ->html(sprintf(
            '<p>A new feature has been added:</p>
            <p><strong>Feature Title:</strong> %s</p>
            <p><strong>Description:</strong> %s</p>
            <p><strong>Feature tag:</strong> %s</p>
            <p><strong>Created By:</strong> %s</p>
            <p><strong>Email:</strong> %s</p>',
            $feature->getTitle(),
            $feature->getDescription(),
            $feature->getTag(),
            $feature->getCreatedBy()->getDisplayName(),
            $feature->getCreatedBy()->getEmail()
        ));

    $this->mailer->send($email);
    }
}
