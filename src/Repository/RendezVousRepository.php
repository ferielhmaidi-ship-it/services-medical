<?php

namespace App\Repository;

use App\Entity\RendezVous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RendezVous>
 */
class RendezVousRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RendezVous::class);
    }

    /**
     * Find appointments by date range
     */
    public function findByDateRange(\DateTime $startDate, \DateTime $endDate)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.appointmentDate >= :startDate')
            ->andWhere('r.appointmentDate < :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('r.appointmentDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all pending appointments for today
     */
    public function findTodayPending()
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        return $this->createQueryBuilder('r')
            ->andWhere('r.appointmentDate >= :today')
            ->andWhere('r.appointmentDate < :tomorrow')
            ->andWhere('r.statut = :statut')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('statut', 'en_attente')
            ->orderBy('r.appointmentDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find appointments by patient
     */
    public function findByPatient($patientId)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.patient = :patientId')
            ->setParameter('patientId', $patientId)
            ->orderBy('r.appointmentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find appointments by doctor
     */
    public function findByDoctor($doctorId)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.doctor = :doctorId')
            ->setParameter('doctorId', $doctorId)
            ->orderBy('r.appointmentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search appointments for a given doctor with an optional search term.
     * The search term is matched against patient first/last name and the appointment message.
     *
     * @param mixed $doctorId
     * @param string|null $search
     * @return RendezVous[]
     */
    public function searchByDoctor($doctorId, ?string $search = null, ?string $statut = null)
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.doctor = :doctorId')
            ->setParameter('doctorId', $doctorId);

        if ($search !== null && trim($search) !== '') {
            $qb->leftJoin('r.patient', 'p')
               ->andWhere('p.firstName LIKE :q OR p.lastName LIKE :q OR r.message LIKE :q')
               ->setParameter('q', '%' . trim($search) . '%');
        }

        if ($statut !== null && trim($statut) !== '') {
            $qb->andWhere('r.statut = :statut')
               ->setParameter('statut', trim($statut));
        }

        return $qb
            ->orderBy('r.appointmentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
