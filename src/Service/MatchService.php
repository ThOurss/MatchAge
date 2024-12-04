<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserMatch;
use App\Entity\MatchUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class MatchService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findMatch(UserInterface $currentUser): ?User
    {
        // Marquer l'utilisateur comme recherchant
        $currentUser->setSearching(true);
        $this->entityManager->flush();

        // Chercher un autre utilisateur en recherche
        $potentialMatch = $this->entityManager->getRepository(User::class)->findSearchingUser($currentUser->getId());

        // Si un match est trouvé, créer un "Match"
        if ($potentialMatch) {
            $match = new UserMatch();
            $this->entityManager->persist($match);

            // Associer les deux utilisateurs au match
            $matchUser1 = new MatchUser($currentUser, $match);
            $matchUser2 = new MatchUser($potentialMatch, $match);

            $this->entityManager->persist($matchUser1);
            $this->entityManager->persist($matchUser2);

            // Marquer les deux utilisateurs comme non-recherchant
            $currentUser->setSearching(false);
            $potentialMatch->setSearching(false);

            $this->entityManager->flush();

            return $potentialMatch;
        }

        return null; // Aucun utilisateur trouvé
    }
}
