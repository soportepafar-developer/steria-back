<?php

namespace App\Module\Auth\Security;

use App\Module\User\Entity\Usuario;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof Usuario) {
            return;
        }

        if (!$user->isActivo()) {
            throw new CustomUserMessageAuthenticationException('Tu cuenta ha sido desactivada. Por favor, contacta al administrador.');
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        // No checks needed post-auth right now
    }
}
