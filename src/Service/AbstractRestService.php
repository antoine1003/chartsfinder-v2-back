<?php

namespace App\Service;

use App\Dto\SearchCriteriaDto;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

abstract class AbstractRestService
{
    protected EntityRepository $repository;
    public function __construct(
        protected string $entityClass,
        protected EntityManagerInterface $entityManager
    )
    {
        $this->repository = $this->entityManager->getRepository($this->entityClass);
    }


    public function find(int $id): ?object
    {
        return $this->repository->find($id);
    }


    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findBy(array $criteria): array
    {
        return $this->repository->findBy($criteria);
    }

    public function save(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function delete(object $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    /**
     * Search for items based on criteria With a like operator or IN operator
     */
    public function search(SearchCriteriaDto $searchCriteriaDto): array
    {
        $queryBuilder = $this->repository->createQueryBuilder('e');
        $query = $searchCriteriaDto->getQuery();

        foreach ($searchCriteriaDto->getProperties() as $property) {
            if (!property_exists($this->entityClass, $property)) {
                throw new BadRequestHttpException("Property '$property' does not exist in entity class '{$this->entityClass}'");
            }

            $queryBuilder->andWhere("e.$property LIKE :{$property}")
                ->setParameter($property, '%' . $query . '%');
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
