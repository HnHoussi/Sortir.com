<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isActive()) {
            // Custom error shown on login page
            throw new CustomUserMessageAccountStatusException(
                'Votre compte est désactivé. Veuillez contacter un administrateur.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Here you could check e.g. if the user changed password recently
    }
}
