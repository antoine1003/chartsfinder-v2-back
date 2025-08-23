<?php

namespace App\Service;

use App\Entity\Airport;
use App\Entity\Chart;
use App\Entity\ChartReport;
use App\Entity\User;
use App\Event\ChartReportedEvent;
use App\Exception\ChartAlreadyReportedException;
use App\Repository\AirportRepository;
use App\Repository\ChartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * @extends AbstractRestService<ChartRepository>
 */
class ChartRestService extends AbstractRestService
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected Security $security,
        private readonly EventDispatcherInterface $eventDispatcher,
    )
    {
        parent::__construct(Chart::class, $entityManager);
    }


    // Block finAll to avoid returning all airports
    public function findAll(): array
    {
        throw new MethodNotAllowedHttpException(
            ['GET'],
            'This method is not allowed. Use search instead.'
        );
    }



    public function reportChart(Chart $chart): void
    {
        /**
         * @var User $user
         */
        $user = $this->security->getUser();
        $userReports = $user->getChartReports();
        // User can report a chart only once a day
        foreach ($userReports as $report) {
            if ($report->getChart() === $chart) {
                $now = new \DateTimeImmutable();
                $reportDate = $report->getCreatedAt();
                $interval = $now->diff($reportDate);
                if ($interval->days < 1) {
                    throw new ChartAlreadyReportedException();
                }
            }
        }

        $chartReport = new ChartReport();
        $chartReport->setChart($chart);
        $chartReport->setUser($user);
        $this->entityManager->persist($chartReport);
        $this->entityManager->flush();

        $event = new ChartReportedEvent($chartReport);
        $this->eventDispatcher->dispatch($event);
    }
}
