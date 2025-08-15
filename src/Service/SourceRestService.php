<?php

namespace App\Service;

use App\Entity\Airport;
use App\Entity\Chart;
use App\Entity\Source;
use App\Repository\AirportRepository;
use App\Repository\SourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * @extends AbstractRestService<SourceRepository>
 */
class SourceRestService extends AbstractRestService
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    )
    {
        parent::__construct(Source::class, $entityManager);
    }


}
