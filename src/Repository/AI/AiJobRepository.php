<?php

declare(strict_types=1);

namespace App\Repository\AI;

use App\Entity\AI\AiJob;
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
        $this->_em->persist($aiJob);
        $this->_em->flush();
    }

    /**
     * @return AiJob[]
     */
    public function findLatestByUserId(string $userId, int $limit = 10): array
    {
        return $this->createQueryBuilder('j')
            ->where('j.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('j.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns statistics for AI jobs grouped by status
     * 
     * @return array<array{
     *    status: string,
     *    count: int,
     *    avgDuration: float
     * }>
     */
    public function getJobStatistics(string $userId): array
    {
        return $this->createQueryBuilder('j')
            ->select('j.status as status, COUNT(j.id) as count, AVG(j.durationMs) as avgDuration')
            ->where('j.userId = :userId')
            ->setParameter('userId', $userId)
            ->groupBy('j.status')
            ->getQuery()
            ->getResult();
    }
} 