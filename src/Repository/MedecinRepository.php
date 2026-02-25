<?php

namespace App\Repository;

use App\Entity\Medecin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Medecin>
 */
class MedecinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Medecin::class);
    }

    //    /**
    //     * @return Medecin[] Returns an array of Medecin objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Medecin
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


     /**
     * Build a filtered QueryBuilder for the admin medecin list.
     * Used by KnpPaginator — returns a QueryBuilder, NOT an array.
     *
     * @param string $search    Free-text search on name, email, CIN
     * @param string $status    'active' | 'inactive' | '' (all)
     * @param string $verified  'verified' | 'unverified' | '' (all)
     * @param string $specialty Exact specialty name | '' (all)
     */
    public function searchDoctors(string $name = null, string $specialty = null, string $governorate = null): array
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->where('m.isActive = :active')
            ->setParameter('active', true);

        if ($name) {
            $queryBuilder->andWhere('(m.firstName LIKE :name OR m.lastName LIKE :name OR CONCAT(m.firstName, \' \', m.lastName) LIKE :name)')
                ->setParameter('name', '%' . $name . '%');
        }

        if ($specialty) {
            $queryBuilder->andWhere('m.specialty = :specialty')
                ->setParameter('specialty', $specialty);
        }

        if ($governorate) {
            $queryBuilder->andWhere('m.governorate = :governorate')
                ->setParameter('governorate', $governorate);
        }

        return $queryBuilder->orderBy('m.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }



        public function createFilteredQueryBuilder(
        string $search    = '',
        string $status    = '',
        string $verified  = '',
        string $specialty = ''
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('m');

        // ── Free-text search (name, email, CIN) ──────────────────────────────
        if ($search !== '') {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(m.firstName)', ':search'),
                    $qb->expr()->like('LOWER(m.lastName)',  ':search'),
                    $qb->expr()->like('LOWER(m.email)',     ':search'),
                    $qb->expr()->like('LOWER(m.cin)',       ':search'),
                )
            )->setParameter('search', '%' . strtolower($search) . '%');
        }

        // ── Status filter ─────────────────────────────────────────────────────
        if ($status === 'active') {
            $qb->andWhere('m.isActive = :active')->setParameter('active', true);
        } elseif ($status === 'inactive') {
            $qb->andWhere('m.isActive = :active')->setParameter('active', false);
        }

        // ── Verification filter ───────────────────────────────────────────────
        if ($verified === 'verified') {
            $qb->andWhere('m.isVerified = :verified')->setParameter('verified', true);
        } elseif ($verified === 'unverified') {
            $qb->andWhere('m.isVerified = :verified')->setParameter('verified', false);
        }

        // ── Specialty filter ──────────────────────────────────────────────────
        if ($specialty !== '') {
            $qb->andWhere('m.specialty = :specialty')->setParameter('specialty', $specialty);
        }

        // ── Default sort ──────────────────────────────────────────────────────
        $qb->orderBy('m.id', 'DESC');

        return $qb;
    }


    public function getFilterOptions(): array
    {
        $allMedecins = $this->findBy(['isActive' => true]);
        $specialties = [];
        $governorates = [];

        foreach ($allMedecins as $medecin) {
            if ($medecin->getSpecialty()) {
                $specialties[$medecin->getSpecialty()] = $medecin->getSpecialty();
            }
            if ($medecin->getGovernorate()) {
                $governorates[$medecin->getGovernorate()] = $medecin->getGovernorate();
            }
        }

        sort($specialties);
        sort($governorates);

        return [
            'specialties' => $specialties,
            'governorates' => $governorates,
        ];
    }
}
