<?php

namespace App\Service;

use App\Entity\Feature;
use App\Entity\FeatureVote;
use App\Entity\User;
use App\Repository\AirportRepository;
use App\Repository\FeatureRepository;
use App\Repository\FeatureVoteRepository;
use App\Security\Voter\FeatureVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends AbstractRestService<AirportRepository>
 */
class FeatureRestService extends AbstractRestService
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        private readonly FeatureRepository $featureRepository,
        private readonly Security $security,
        private readonly FeatureVoteRepository $featureVoteRepository
    )
    {
        parent::__construct(Feature::class, $entityManager);
    }


    // Block finAll to avoid returning all airports
    public function findAll(): array
    {
        /**
         * @var User $user
         */
        $user = $this->security->getUser();
        $data = $this->featureRepository->getAllFeaturesWithVotes($user);

        // if isAnonymous is true, we should not return the createdBy field
        foreach ($data as &$feature) {
            if ($feature['isAnonymous']) {
                $feature['createdBy'] = null;
            }
        }
        return $data;
    }

    /**
     * @return array{upvotes: int, downvotes: int, userVote: string|null}
     */
    public function handleVote(Feature $feature, User $user, string $type): array
    {
        if (!in_array($type, ['up', 'down', 'remove'], true)) {
            throw new \InvalidArgumentException('Invalid vote type');
        }

        $existingVote = $this->featureVoteRepository->findOneBy([
            'feature' => $feature,
            'user' => $user,
        ]);

        if ($type === 'remove') {
            $this->security->isGranted(FeatureVoter::DELETE, $feature);
            if ($existingVote) {
                $this->entityManager->remove($existingVote);
                $this->entityManager->flush();
            }
        } else {
            $this->security->isGranted(FeatureVoter::UP_VOTE, $feature);
            if ($existingVote) {
                $existingVote->setVote($type);
            } else {
                $vote = new FeatureVote();
                $vote->setFeature($feature);
                $vote->setUser($user);
                $vote->setVote($type);
                $this->entityManager->persist($vote);
            }

            $this->entityManager->flush();
        }

        return [
            'upvotes' => $this->featureVoteRepository->count(['feature' => $feature, 'vote' => 'up']),
            'downvotes' => $this->featureVoteRepository->count(['feature' => $feature, 'vote' => 'down']),
            'userVote' => $this->featureVoteRepository->findOneBy(['feature' => $feature, 'user' => $user])?->getVote(),
        ];
    }
}
