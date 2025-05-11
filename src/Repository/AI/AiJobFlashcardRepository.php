<?php

declare(strict_types=1);

namespace App\Repository\AI;

use App\Entity\AI\AiJob;
use App\Entity\AI\AiJobFlashcard;
use App\Enum\AI\FlashcardStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AiJobFlashcard>
 *
 * @method AiJobFlashcard|null find($id, $lockMode = null, $lockVersion = null)
 * @method AiJobFlashcard|null findOneBy(array $criteria, array $orderBy = null)
 * @method AiJobFlashcard[]    findAll()
 * @method AiJobFlashcard[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AiJobFlashcardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AiJobFlashcard::class);
    }

    /**
     * @return AiJobFlashcard[]
     */
    public function findByAiJobId(int $aiJobId): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.aiJob = :aiJobId')
            ->setParameter('aiJobId', $aiJobId)
            ->orderBy('f.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns statistics for flashcards grouped by status
     * 
     * @return array<array{
     *    status: string,
     *    count: int
     * }>
     */
    public function getFlashcardStatistics(string $userId): array
    {
        return $this->createQueryBuilder('f')
            ->select('f.status as status, COUNT(f.id) as count')
            ->join('f.aiJob', 'j')
            ->where('j.userId = :userId')
            ->setParameter('userId', $userId)
            ->groupBy('f.status')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return AiJobFlashcard[]
     */
    public function findPaginatedByJob(AiJob $job, int $page, int $perPage): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.aiJob = :job')
            ->setParameter('job', $job)
            ->orderBy('f.createdAt', Criteria::ASC)
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function countByJob(AiJob $job): int
    {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->andWhere('f.aiJob = :job')
            ->setParameter('job', $job)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return AiJobFlashcard[]
     */
    public function findByJobAndStatus(AiJob $job, FlashcardStatus $status): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.aiJob = :job')
            ->andWhere('f.status = :status')
            ->setParameter('job', $job)
            ->setParameter('status', $status)
            ->orderBy('f.createdAt', Criteria::ASC)
            ->getQuery()
            ->getResult();
    }
} 