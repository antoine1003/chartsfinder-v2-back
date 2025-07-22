<?php

namespace App\Service;

use App\Entity\Airport;
use App\Repository\AirportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * @extends AbstractRestService<AirportRepository>
 */
class AirportRestService extends AbstractRestService
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    )
    {
        parent::__construct(Airport::class, $entityManager);
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
