<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        // Vérifie si l'utilisateur est marqué comme supprimé
        if (method_exists($user, 'isDelete') && $user->isDelete()) {
            // Vous pouvez personnaliser ce message
            throw new CustomUserMessageAccountStatusException('Votre compte a été supprimé. Contactez un administrateur.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Ici, vous pouvez ajouter d'autres vérifications après l'authentification
    }
}
