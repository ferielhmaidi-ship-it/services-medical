<?php

namespace App\Repository;

use App\Entity\Magazine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Magazine>
 */
class MagazineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Magazine::class);
    }

    /**
     * @return Magazine[] Returns an array of Magazine objects
     */
    public function findBySearch(string $term): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.title LIKE :term OR m.description LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('m.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
