<?php

namespace App\Repository;

use App\Entity\AiJob;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AiJob>
 *
 * @method AiJob|null find($id, $lockMode = null, $lockVersion = null)
 * @method AiJob|null findOneBy(array $criteria, array $orderBy = null)
 * @method AiJob[]    findAll()
 * @method AiJob[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AiJobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AiJob::class);
    }

    public function save(AiJob $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AiJob $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return AiJob[] Returns an array of user's AI jobs
     */
    public function findByUser(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.user = :user')
            ->setParameter('user', $user)
            ->orderBy('j.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return AiJob[] Returns an array of pending AI jobs
     */
    public function findPendingJobs(): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.status = :status')
            ->setParameter('status', AiJob::STATUS_PENDING)
            ->orderBy('j.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns statistics for AI jobs
     */
    public function getJobStatistics(User $user): array
    {
        $qb = $this->createQueryBuilder('j')
            ->select('j.status, COUNT(j.id) as count, AVG(j.durationMs) as avgDuration')
            ->andWhere('j.user = :user')
            ->setParameter('user', $user)
            ->groupBy('j.status');

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns total number of flashcards generated
     */
    public function getTotalFlashcardsGenerated(User $user): int
    {
        $result = $this->createQueryBuilder('j')
            ->select('SUM(j.flashcardsCount) as total')
            ->andWhere('j.user = :user')
            ->andWhere('j.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', AiJob::STATUS_COMPLETED)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result;
    }
} 