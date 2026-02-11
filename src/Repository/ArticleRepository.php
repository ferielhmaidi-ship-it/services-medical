<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @return Article[] Returns an array of Article objects
     */
    public function findByMagazineAndSearch(int $magazineId, string $term): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.magazine = :magId')
            ->andWhere('a.title LIKE :term OR a.resume LIKE :term')
            ->setParameter('magId', $magazineId)
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('a.datePub', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[] Returns an array of Article objects (global search)
     */
    public function findByGlobalSearch(string $term): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.magazine', 'm')
            ->andWhere('a.title LIKE :term OR a.resume LIKE :term OR a.auteur LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('a.datePub', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
