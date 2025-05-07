<?php

namespace App\Repository;

use App\Entity\RateLimit;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RateLimit>
 *
 * @method RateLimit|null find($id, $lockMode = null, $lockVersion = null)
 * @method RateLimit|null findOneBy(array $criteria, array $orderBy = null)
 * @method RateLimit[]    findAll()
 * @method RateLimit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RateLimitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RateLimit::class);
    }

    public function save(RateLimit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RateLimit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find current rate limit window for user
     */
    public function findCurrentWindow(User $user, \DateTimeImmutable $now): ?RateLimit
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.windowStart <= :now')
            ->andWhere('r.windowStart > :windowEnd')
            ->setParameter('user', $user)
            ->setParameter('now', $now)
            ->setParameter('windowEnd', $now->modify('-24 hours'))
            ->orderBy('r.windowStart', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get usage statistics for a user
     */
    public function getUserStatistics(User $user, \DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        return $this->createQueryBuilder('r')
            ->select('SUM(r.requestCount) as totalRequests')
            ->addSelect('SUM(r.textCharacters) as totalCharacters')
            ->addSelect('SUM(r.flashcardCount) as totalFlashcards')
            ->andWhere('r.user = :user')
            ->andWhere('r.windowStart >= :start')
            ->andWhere('r.windowStart <= :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Clean up old rate limit records
     */
    public function removeOldRecords(\DateTimeImmutable $before): int
    {
        return $this->createQueryBuilder('r')
            ->delete()
            ->andWhere('r.windowStart < :before')
            ->setParameter('before', $before)
            ->getQuery()
            ->execute();
    }
} 