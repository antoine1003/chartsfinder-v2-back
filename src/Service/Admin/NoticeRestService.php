<?php

namespace App\Service\Admin;

use App\Entity\Airport;
use App\Entity\Chart;
use App\Entity\ChartReport;
use App\Entity\User;
use App\Entity\UserNotice;
use App\Event\ChartReportedEvent;
use App\Exception\ChartAlreadyReportedException;
use App\Repository\AirportRepository;
use App\Repository\ChartRepository;
use App\Repository\UserNoticeRepository;
use App\Service\AbstractRestService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * @extends AbstractRestService<UserNoticeRepository>
 */
class NoticeRestService extends AbstractRestService
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected Security $security
    )
    {
        parent::__construct(UserNotice::class, $entityManager);
    }

}
