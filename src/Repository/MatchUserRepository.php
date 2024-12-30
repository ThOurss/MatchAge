<?php

namespace App\Repository;

use App\Entity\MatchUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MatchUser>
 */
class MatchUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MatchUser::class);
    }

    public function isMatched(User $user1, User $user2): bool
    {
        $qb = $this->createQueryBuilder('u');
        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->andX('u.user1 = :user1', 'u.user2 = :user2'),
                $qb->expr()->andX('u.user1 = :user2', 'u.user2 = :user1')
            )
        )
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2);

        return (bool)$qb->getQuery()->getOneOrNullResult();
    }

    public function findLastMatchForUser(int $userId): ?MatchUser
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user1 = :userId OR m.user2 = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('m.matchedAt', 'DESC')  // Correcte : Trier par la colonne 'matchedAt'
            ->setMaxResults(1)  // Limiter à un seul résultat
            ->getQuery()
            ->getOneOrNullResult();  // Récupérer le dernier match ou null si aucun match trouvé
    }

    public function findOtherUserInMatch(int $matchId, int $userId): ?User
    {

        // Rechercher le match spécifique par son ID
        $match = $this->createQueryBuilder('m')
            ->andWhere('m.id = :matchId')
            ->setParameter('matchId', $matchId)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$match) {
            return null;  // Aucun match trouvé
        }

        // Vérifier si l'utilisateur actuel est user1 ou user2 et retourner l'autre
        if ($match->getUser1()->getId() === $userId) {

            return $match->getUser2();  // Si l'utilisateur actuel est user1, retourner user2
        } elseif ($match->getUser2()->getId() === $userId) {
            
            return $match->getUser1();  // Si l'utilisateur actuel est user2, retourner user1
        }

        return null;  // Si l'utilisateur actuel n'est dans aucun des deux slots
    }



    //    /**
    //     * @return MatchUser[] Returns an array of MatchUser objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?MatchUser
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
