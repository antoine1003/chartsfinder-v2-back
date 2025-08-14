<?php

namespace App\Security\Voter;

use App\Entity\Enum\FeatureStatusEnum;
use App\Entity\Feature;
use App\Entity\Preset;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class FeatureVoter extends Voter
{
    public const UP_VOTE = 'feature_upvote';
    public const DOWN_VOTE = 'feature_downvote';
    public const REMOVE_VOTE = 'feature_remove_vote';
    public const EDIT = 'feature_edit';
    public const VIEW = 'feature_view';
    public const DELETE = 'feature_delete';

    public function __construct(
        private Security $security
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Check if the attribute is one of the defined constants
        if (!in_array($attribute, [self::UP_VOTE, self::DOWN_VOTE, self::REMOVE_VOTE, self::EDIT, self::VIEW, self::DELETE], true)) {
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
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::VIEW,
            self::UP_VOTE,
            self::DOWN_VOTE,
            self::REMOVE_VOTE => $subject->getStatus() !== FeatureStatusEnum::ABANDONED,

            self::EDIT,
            self::DELETE => $subject->getCreatedBy()?->getId() === $user->getId() || $user->isAdmin(),
            default => false,
        };

    }
}
