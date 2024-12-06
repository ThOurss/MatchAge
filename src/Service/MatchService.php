<?php

namespace App\Service;

use App\Entity\MatchUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;

class MatchService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findMatch(User $currentUser): ?User
    {
        // Rechercher un utilisateur disponible
        $query = $this->entityManager->createQuery(
            'SELECT u FROM App\Entity\User u
             WHERE u.isSearching = true
             AND u.id != :currentUserId'
        );
        $query->setParameter('currentUserId', $currentUser->getId());
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }
    public function searchForMatch(User $user): ?MatchUser{
        $this->entityManager->beginTransaction();

        try {
            // 1. Vérifier si l'utilisateur est déjà en recherche
            if ($user->isSearching()) {
                throw new \Exception('L\'utilisateur est déjà en recherche.');
            }

            // 2. Marquer l'utilisateur comme en recherche
            $user->setSearching(true);
            $this->entityManager->flush(); // Vérifie la version

            // 3. Trouver un autre utilisateur en recherche
            $query = $this->entityManager->createQuery(
                'SELECT u FROM App\Entity\User u WHERE u.isSearching = true AND u.id != :userId'
            )->setParameter('userId', $user->getId());

            $potentialMatch = $query->setMaxResults(1)->getOneOrNullResult();

            // 4. Si aucun utilisateur n'est trouvé, terminer la transaction
            if (!$potentialMatch) {
                $this->entityManager->commit();
                return null;
            }

            // 5. Créer un match entre les deux utilisateurs
            $match = new MatchUser($user, $potentialMatch);


            // 6. Marquer les deux utilisateurs comme non en recherche
            $user->setSearching(false);
            $potentialMatch->setSearching(false);

            $this->entityManager->persist($match);
            $this->entityManager->flush();

            $this->entityManager->commit();

            return $match;
        } catch (OptimisticLockException $e) {
            $this->entityManager->rollback();
            throw new \Exception('Conflit détecté, veuillez réessayer.');
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
}
