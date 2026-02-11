<?php

namespace App\Repository;

use App\Entity\Medecin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
