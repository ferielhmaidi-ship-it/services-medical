<?php

namespace App\Repository;

use App\Entity\Feedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Feedback>
 */
class FeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feedback::class);
    }

    /**
     * Find feedback by appointment (RendezVous)
     */
    public function findByAppointment($appointmentId)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.rendezVous = :appointmentId')
            ->setParameter('appointmentId', $appointmentId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all feedback for a doctor
     */
    public function findByDoctor($doctorId)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.medecin = :doctorId')
            ->setParameter('doctorId', $doctorId)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all feedback from a patient
     */
    public function findByPatient($patientId)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.patient = :patientId')
            ->setParameter('patientId', $patientId)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
