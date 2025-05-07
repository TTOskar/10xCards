<?php

namespace App\Repository;

use App\Entity\AiJob;
use App\Entity\AiJobFlashcard;
use App\Entity\User;
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

    public function save(AiJobFlashcard $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AiJobFlashcard $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return AiJobFlashcard[] Returns an array of flashcards for an AI job
     */
    public function findByAiJob(AiJob $aiJob): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.aiJob = :aiJob')
            ->setParameter('aiJob', $aiJob)
            ->orderBy('f.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns statistics for flashcards by status
     */
    public function getFlashcardStatistics(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->select('f.status, COUNT(f.id) as count')
            ->join('f.aiJob', 'j')
            ->andWhere('j.user = :user')
            ->setParameter('user', $user)
            ->groupBy('f.status')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return AiJobFlashcard[] Returns an array of accepted flashcards for a user
     */
    public function findAcceptedFlashcards(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->join('f.aiJob', 'j')
            ->andWhere('j.user = :user')
            ->andWhere('f.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', AiJobFlashcard::STATUS_ACCEPTED)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return AiJobFlashcard[] Returns an array of edited flashcards for a user
     */
    public function findEditedFlashcards(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->join('f.aiJob', 'j')
            ->andWhere('j.user = :user')
            ->andWhere('f.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', AiJobFlashcard::STATUS_EDITED)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 