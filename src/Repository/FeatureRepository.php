<?php

namespace App\Repository;

use App\Entity\Enum\FeatureStatusEnum;
use App\Entity\Feature;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Feature>
 */
class FeatureRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
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
            f.tag,
            f.is_anonymous as isAnonymous,
            u.display_name AS createdBy,
            f.created_at as createdAt,
            SUM(CASE WHEN fv.vote = :up THEN 1 ELSE 0 END) AS upVotes,
            SUM(CASE WHEN fv.vote = :down THEN 1 ELSE 0 END) AS downVotes,
            MAX(CASE WHEN fv.user_id = :userId THEN fv.vote ELSE \'none\' END) AS userVote
        FROM feature f
        LEFT JOIN feature_vote fv ON f.id = fv.feature_id
        JOIN user u ON f.created_by_id = u.id
    ';
        $params = [
            'up' => 'up',
            'down' => 'down',
            'userId' => $currentUser->getId(),
        ];
        if (!$currentUser->isAdmin()) {
            $sql .= ' WHERE f.status != :archived';
            $params['status'] = FeatureStatusEnum::ABANDONED;
        } else {
            $sql .= ' WHERE 1=1';
        }

        $sql .= ' GROUP BY f.id';


        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery($params);

        return $resultSet->fetchAllAssociative();
    }
}
