<?php

namespace App\Repository;

use App\Entity\Card;
use App\Entity\Deck;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Card>
 *
 * @method Card|null find($id, $lockMode = null, $lockVersion = null)
 * @method Card|null findOneBy(array $criteria, array $orderBy = null)
 * @method Card[]    findAll()
 * @method Card[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    public function save(Card $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Card $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Card[] Returns an array of active cards for a deck
     */
    public function findActiveCardsForDeck(Deck $deck): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.deck = :deck')
            ->andWhere('c.isDeleted = :isDeleted')
            ->setParameter('deck', $deck)
            ->setParameter('isDeleted', false)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Card[] Returns an array of cards due for review
     */
    public function findDueCardsForUser(User $user, \DateTime $date): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.deck', 'd')
            ->andWhere('d.user = :user')
            ->andWhere('c.dueDate <= :date')
            ->andWhere('c.isDeleted = :isDeleted')
            ->andWhere('d.isDeleted = :isDeleted')
            ->setParameter('user', $user)
            ->setParameter('date', $date)
            ->setParameter('isDeleted', false)
            ->orderBy('c.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Card[] Returns an array of cards pending deletion
     */
    public function findCardsPendingDeletion(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isDeleted = :isDeleted')
            ->andWhere('c.deletedAt IS NOT NULL')
            ->setParameter('isDeleted', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns count of cards by source type for a user
     */
    public function countCardsBySourceForUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.source, COUNT(c.id) as count')
            ->join('c.deck', 'd')
            ->andWhere('d.user = :user')
            ->andWhere('c.isDeleted = :isDeleted')
            ->andWhere('d.isDeleted = :isDeleted')
            ->setParameter('user', $user)
            ->setParameter('isDeleted', false)
            ->groupBy('c.source')
            ->getQuery()
            ->getResult();
    }
} 