<?php

namespace App\Service;

use App\Entity\Airport;
use App\Entity\Chart;
use App\Repository\AirportRepository;
use App\Repository\ChartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * @extends AbstractRestService<ChartRepository>
 */
class ChartRestService extends AbstractRestService
{
    public function __construct(
        protected EntityManagerInterface $entityManager
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
}
