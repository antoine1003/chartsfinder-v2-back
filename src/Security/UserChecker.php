<?php

namespace App\Security;

use App\Entity\User;
use App\Exception\EmailNotValidatedException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isEmailValidated()) {
            // Message shown at login failure (translatable)
            throw new UnauthorizedHttpException('checkEmail', 'checkEmail');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // no-op
    }
}
