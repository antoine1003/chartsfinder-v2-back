<?php

namespace App\Service;

use App\Dto\SearchCriteriaDto;
use App\Entity\Airport;
use App\Repository\AirportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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


    public function search(SearchCriteriaDto $searchCriteriaDto): array
    {
        $queryBuilder = $this->repository->createQueryBuilder('e');
        $query = $searchCriteriaDto->getQuery();

        foreach ($searchCriteriaDto->getProperties() as $property) {
            if (!property_exists($this->entityClass, $property)) {
                throw new BadRequestHttpException("Property '$property' does not exist in entity class '{$this->entityClass}'");
            }
            $query = trim($query);
            if ($query === '') {
                continue;
            }

            // Lowercase the property name to match the entity field
            $query = strtolower($query);

            $queryBuilder->orWhere("LOWER(e.$property) LIKE :query")
                ->setParameter('query', "%$query%");
        }

        // And has charts associated
        $queryBuilder->andWhere('e.charts IS NOT EMPTY');

        return $queryBuilder->getQuery()->getResult();
    }
}
