<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserNotice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserNotice>
 */
class UserNoticeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserNotice::class);
    }

    public function getUnreadNoticesForUser(User $user)
    {
        $qb = $this->createQueryBuilder('n')
            ->leftJoin('n.userNoticeDismissals', 'd', 'WITH', 'd.user = :user')
            ->where('n.isActive = :active')
            ->andWhere('d.id IS NULL')
            ->setParameter('user', $user)
            ->setParameter('active', true);

        return $qb->getQuery()->getResult();
    }
}
