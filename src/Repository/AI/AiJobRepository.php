<?php

declare(strict_types=1);

namespace App\Repository\AI;

use App\Entity\AI\AiJob;
use App\Enum\AI\JobStatus;
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

    public function save(AiJob $aiJob): void
    {
        $em = $this->getEntityManager();
        $em->persist($aiJob);
        $em->flush();
    }

    public function countRecentRequestsForUser(int $userId): int
    {
        $oneMinuteAgo = new \DateTime('-1 minute');
        
        return $this->createQueryBuilder('j')
            ->select('COUNT(j.id)')
            ->where('j.userId = :userId')
            ->andWhere('j.createdAt >= :oneMinuteAgo')
            ->setParameter('userId', $userId)
            ->setParameter('oneMinuteAgo', $oneMinuteAgo)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<array{
     *    status: string,
     *    count: int
     * }>
     */
    public function getJobStatistics(int $userId): array
    {
        return $this->createQueryBuilder('j')
            ->select('j.status as status, COUNT(j.id) as count')
            ->where('j.userId = :userId')
            ->setParameter('userId', $userId)
            ->groupBy('j.status')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return AiJob[]
     */
    public function findRecentByUser(int $userId, int $limit = 10): array
    {
        return $this->createQueryBuilder('j')
            ->where('j.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('j.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLatestByUser(int $userId): ?AiJob
    {
        return $this->createQueryBuilder('j')
            ->where('j.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('j.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
} 