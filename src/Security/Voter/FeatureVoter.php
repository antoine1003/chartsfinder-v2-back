<?php

namespace App\Security\Voter;

use App\Entity\Enum\FeatureStatusEnum;
use App\Entity\Feature;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class FeatureVoter extends Voter
{
    public const CREATE = 'create';
    public const UP_VOTE = 'feature_upvote';
    public const DOWN_VOTE = 'feature_downvote';
    public const REMOVE_VOTE = 'feature_remove_vote';
    public const READ = 'read';
    public const READ_ALL = 'read_all';
    public const DELETE = 'delete';
    public const UPDATE = 'update';
    public const SEARCH = 'search';

    public function __construct()
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($subject === Feature::class && in_array($attribute,[self::READ_ALL, self::SEARCH] )) {
            // Special case for viewing the Feature class
            return true;
        }
        // Check if the attribute is one of the defined constants
        if (!in_array($attribute, [self::UP_VOTE, self::DOWN_VOTE, self::REMOVE_VOTE, self::UPDATE, self::READ, self::DELETE, self::CREATE], true)) {
            return false;
        }

        // Check if the subject is an instance of Preset
        if (!$subject instanceof Feature) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param Feature $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /**
         * @var User $user
         */
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::READ_ALL:
            case self::SEARCH:
            case self::READ:
            case self::CREATE:
                return true;

            case self::UP_VOTE:
            case self::DOWN_VOTE:
            case self::REMOVE_VOTE:
                return $subject->getStatus() !== FeatureStatusEnum::ABANDONED;

            case self::DELETE:
            case self::UPDATE:
                // Only allow deletion if the user is the owner of the feature
                return $user->isAdmin();
            default:
                return false;
        }

    }
}
