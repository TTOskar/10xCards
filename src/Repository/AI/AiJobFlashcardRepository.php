<?php

declare(strict_types=1);

namespace App\Repository\AI;

use App\Entity\AI\AiJobFlashcard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
} 