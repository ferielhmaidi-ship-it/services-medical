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
     * Search articles inside one magazine
     * @return Article[]
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
     *  Global search
     * @return Article[]
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

    /**
     * ⬅ Find previous article in same magazine
     */
    public function findPreviousInMagazine(int $magazineId, int $currentId): ?Article
    {
        return $this->createQueryBuilder('a')
            ->where('a.magazine = :magId')
            ->andWhere('a.id < :currentId')
            ->setParameter('magId', $magazineId)
            ->setParameter('currentId', $currentId)
            ->orderBy('a.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * ➡ Find next article in same magazine
     */
    public function findNextInMagazine(int $magazineId, int $currentId): ?Article
    {
        return $this->createQueryBuilder('a')
            ->where('a.magazine = :magId')
            ->andWhere('a.id > :currentId')
            ->setParameter('magId', $magazineId)
            ->setParameter('currentId', $currentId)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
