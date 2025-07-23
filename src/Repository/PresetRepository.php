<?php

namespace App\Repository;

use App\Entity\Preset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Preset>
 */
class PresetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Preset::class);
    }

    public function findMine(): array
    {
        return $this->findAll();
    }
}
