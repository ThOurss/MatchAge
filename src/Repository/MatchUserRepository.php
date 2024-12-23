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
