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
        // Treating if two runways are given like 03-21
        if (str_contains($ident, '-')) {
            $idents = explode('-', $ident);
        }
        else {
            $idents = [$ident];
        }
        $qb = $this->createQueryBuilder('r')
            ->where('r.airport = :airport')
            ->setParameter('airport', $airport);

        $condition = $qb->expr()->orX();

        foreach ($idents as $i => $rwy) {
            $condition->add($qb->expr()->like('r.ident', ':ident' . $i));
            $qb->setParameter('ident' . $i, $rwy . '%');
        }


        $qb->andWhere($condition);

        return $qb->getQuery()->getResult();
    }
}
