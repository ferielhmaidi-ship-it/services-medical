<?php

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\Medecin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Appointment>
 * @method Appointment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Appointment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Appointment[]    findAll()
 * @method Appointment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    public function save(Appointment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Appointment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find appointments by doctor and date range
     */
    public function findByDoctorAndRange(Medecin $doctor, \DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.doctor = :doctor')
            ->andWhere('a.date >= :startDate')
            ->andWhere('a.date < :endDate')
            ->setParameter('doctor', $doctor)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('a.date', 'ASC')
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

        return $this->createQueryBuilder('a')
            ->andWhere('a.date >= :today')
            ->andWhere('a.date < :tomorrow')
            ->andWhere('a.status = :status')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('status', 'pending')
            ->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find appointments by patient
     */
    public function findByPatient($patientId)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.patient = :patientId')
            ->setParameter('patientId', $patientId)
            ->orderBy('a.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find appointments by doctor
     */
    public function findByDoctor($doctorId)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.doctor = :doctorId')
            ->setParameter('doctorId', $doctorId)
            ->orderBy('a.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search appointments for a given doctor with an optional search term.
     */
    public function searchByDoctor($doctorId, ?string $search = null, ?string $status = null)
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.doctor = :doctorId')
            ->setParameter('doctorId', $doctorId);

        if ($search !== null && trim($search) !== '') {
            $qb->leftJoin('a.patient', 'p')
                ->andWhere('p.firstName LIKE :q OR p.lastName LIKE :q OR a.message LIKE :q')
                ->setParameter('q', '%' . trim($search) . '%');
        }

        if ($status !== null && trim($status) !== '') {
            $qb->andWhere('a.status = :status')
                ->setParameter('status', trim($status));
        }

        return $qb
            ->orderBy('a.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
