<?php

namespace App\Repository;

use App\Entity\Enum\FeatureStatusEnum;
use App\Entity\Feature;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Feature>
 */
class FeatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feature::class);
    }

    /**
     * @throws Exception
     */
    public function getAllFeaturesWithVotes(User $currentUser): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT
            f.id,
            f.title,
            f.description,
            f.status,
            u.display_name AS createdBy,
            SUM(CASE WHEN fv.vote = :up THEN 1 ELSE 0 END) AS upVotes,
            SUM(CASE WHEN fv.vote = :down THEN 1 ELSE 0 END) AS downVotes,
            MAX(CASE WHEN fv.user_id = :userId THEN fv.vote ELSE \'none\' END) AS userVote
        FROM feature f
        LEFT JOIN feature_vote fv ON f.id = fv.feature_id
        JOIN user u ON f.created_by_id = u.id
        WHERE f.status != :archived
        GROUP BY f.id
    ';

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            'up' => 'up',
            'down' => 'down',
            'userId' => $currentUser->getId(),
            'archived' => FeatureStatusEnum::ABANDONED,
        ]);

        return $resultSet->fetchAllAssociative();
    }
}
