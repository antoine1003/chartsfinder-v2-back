<?php

namespace App\EventListener;

use App\Event\ChartReportedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsEventListener]
readonly class ChartReportedListener
{
    public function __construct(
        private MailerInterface $mailer
    )
    {
    }

    public function __invoke(ChartReportedEvent $event): void
    {
        $chartReport = $event->getChartReport();

        // Send email to administrator for Google account registration
        $email = (new Email())
            ->from('noreply@chartsfinder.com')
            ->to("antoine.dautry@gmail.com")
            ->subject('[CF v2] Carte signalée')
            ->html(sprintf(
                '<p>Une carte a été signalée par un utilisateur.</p>
            <ul>
                <li>Utilisateur : %s</li>
                <li>Carte : <a href="%s">%s</a></li>
                <li>Aéroport : %s</li>
                <li>Date : %s</li>
            </ul>',
                $chartReport->getUser()->getEmail(),
                $chartReport->getChart()->getUrl(),
                $chartReport->getChart()->getName(),
                $chartReport->getChart()->getAirport()?->getIcaoCode(),
                $chartReport->getCreatedAt()->format('Y-m-d H:i:s')
            ));

        $this->mailer->send($email);
    }
}
