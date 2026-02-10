<?php
// src/Repository/DocumentRepository.php
namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    public function findAllWithPdfContent(): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.type = :type OR d.type = :type2')
            ->setParameter('type', 'pdf')
            ->setParameter('type2', 'application/pdf')
            ->getQuery()
            ->getResult();
    }
}