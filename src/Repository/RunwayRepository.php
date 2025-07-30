<?php

namespace App\Repository;

use App\Entity\Airport;
use App\Entity\Runway;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Runway>
 */
class RunwayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Runway::class);
    }

    /**
     * @return Runway[] Returns an array of Runway objects
     */
    public function findByIdentLike(?string $ident, Airport $airport): array
    {
        if (empty($ident)) {
            return [];
        }
        return $this->createQueryBuilder('r')
            ->where('r.airport = :airport')
            ->andWhere('r.ident LIKE :ident')
            ->setParameter('airport', $airport)
            ->setParameter('ident', $ident . '%')
            ->getQuery()
            ->getResult();
    }
}
