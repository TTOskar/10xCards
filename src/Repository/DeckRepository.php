<?php

namespace App\Repository;

use App\Entity\Deck;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Deck>
 *
 * @method Deck|null find($id, $lockMode = null, $lockVersion = null)
 * @method Deck|null findOneBy(array $criteria, array $orderBy = null)
 * @method Deck[]    findAll()
 * @method Deck[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeckRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deck::class);
    }

    public function save(Deck $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Deck $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Deck[] Returns an array of active decks for a user
     */
    public function findActiveDecksForUser(User $user): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.user = :user')
            ->andWhere('d.isDeleted = :isDeleted')
            ->setParameter('user', $user)
            ->setParameter('isDeleted', false)
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByNameAndUser(string $name, User $user): ?Deck
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.name = :name')
            ->andWhere('d.user = :user')
            ->andWhere('d.isDeleted = :isDeleted')
            ->setParameter('name', $name)
            ->setParameter('user', $user)
            ->setParameter('isDeleted', false)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Deck[] Returns an array of decks pending deletion
     */
    public function findDecksPendingDeletion(): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.isDeleted = :isDeleted')
            ->andWhere('d.deletedAt IS NOT NULL')
            ->setParameter('isDeleted', true)
            ->getQuery()
            ->getResult();
    }
} 