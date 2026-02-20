<?php
// src/Controller/PatientDashboardController.php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Ordonnance;
use App\Entity\Patient;
use App\Entity\Rapport;
use App\Entity\Medecin;
use Doctrine\ORM\EntityNotFoundException;
use App\Form\PatientProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/patient')]
#[IsGranted('ROLE_PATIENT')]
class PatientDashboardController extends AbstractController
{
    #[Route('/', name: 'patient_dashboard')]
    public function index(): Response
    {
        $patient = $this->getUser();

        return $this->render('patient_dashboard/index.html.twig', [
            'patient' => $patient,
        ]);
    }

    #[Route('/profile', name: 'patient_profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var Patient $patient */
        $patient = $this->getUser();

        $form = $this->createForm(PatientProfileType::class, $patient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle age calculation from date of birth if needed
            if ($patient->getDateOfBirth()) {
                $today = new \DateTime();
                $birthDate = $patient->getDateOfBirth();
                $age = $today->diff($birthDate)->y;
                $patient->setAge($age);
            }

            $entityManager->persist($patient);
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            return $this->redirectToRoute('patient_profile');
        }

        return $this->render('patient_dashboard/profile.html.twig', [
            'patient' => $patient,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/rapports-ordonnances', name: 'patient_reports_prescriptions')]
    public function reportsPrescriptions(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Patient) {
            throw $this->createAccessDeniedException('Acces reserve aux patients.');
        }

        $appointments = $entityManager->getRepository(Appointment::class)->findBy(
            ['patientId' => $user->getId()],
            ['date' => 'DESC', 'startTime' => 'DESC']
        );

        $rapports = $entityManager->getRepository(Rapport::class)->findBy(
            ['patient' => $user],
            ['createdAt' => 'DESC']
        );

        $ordonnances = $entityManager->getRepository(Ordonnance::class)->findBy(
            ['patient' => $user],
            ['dateOrdonnance' => 'DESC']
        );

        $groupsByDoctor = [];

        foreach ($appointments as $appointment) {
            $appointment = $this->resolveAppointment($entityManager, $appointment);
            if (!$appointment instanceof Appointment) {
                continue;
            }

            $doctor = $entityManager->getRepository(Medecin::class)->find($appointment->getDoctorId());
            if (!$doctor instanceof Medecin) {
                continue;
            }

            $doctorId = $doctor->getId() ?? 0;

            if (!isset($groupsByDoctor[$doctorId])) {
                $groupsByDoctor[$doctorId] = [
                    'doctor' => $doctor,
                    'appointments' => [],
                ];
            }

            $groupsByDoctor[$doctorId]['appointments'][$appointment->getId()] = [
                'appointment' => $appointment,
                'rapports' => [],
                'ordonnances' => [],
            ];
        }

        foreach ($rapports as $rapport) {
            $appointment = $this->safeGetAppointmentFromRapport($entityManager, $rapport);
            $doctor = $rapport->getMedecin();
            if (!$doctor instanceof Medecin && $appointment instanceof Appointment) {
                $doctor = $entityManager->getRepository(Medecin::class)->find($appointment->getDoctorId());
            }
            if ($doctor === null || $appointment === null) {
                continue;
            }

            $doctorId = $doctor->getId() ?? 0;
            $appointmentId = $appointment->getId();

            if (!isset($groupsByDoctor[$doctorId])) {
                $groupsByDoctor[$doctorId] = [
                    'doctor' => $doctor,
                    'appointments' => [],
                ];
            }

            if (!isset($groupsByDoctor[$doctorId]['appointments'][$appointmentId])) {
                $groupsByDoctor[$doctorId]['appointments'][$appointmentId] = [
                    'appointment' => $appointment,
                    'rapports' => [],
                    'ordonnances' => [],
                ];
            }

            $groupsByDoctor[$doctorId]['appointments'][$appointmentId]['rapports'][] = $rapport;
        }

        foreach ($ordonnances as $ordonnance) {
            $appointment = $this->safeGetAppointmentFromOrdonnance($entityManager, $ordonnance);
            $doctor = $ordonnance->getMedecin();
            if (!$doctor instanceof Medecin && $appointment instanceof Appointment) {
                $doctor = $entityManager->getRepository(Medecin::class)->find($appointment->getDoctorId());
            }
            if ($doctor === null || $appointment === null) {
                continue;
            }

            $doctorId = $doctor->getId() ?? 0;
            $appointmentId = $appointment->getId();

            if (!isset($groupsByDoctor[$doctorId])) {
                $groupsByDoctor[$doctorId] = [
                    'doctor' => $doctor,
                    'appointments' => [],
                ];
            }

            if (!isset($groupsByDoctor[$doctorId]['appointments'][$appointmentId])) {
                $groupsByDoctor[$doctorId]['appointments'][$appointmentId] = [
                    'appointment' => $appointment,
                    'rapports' => [],
                    'ordonnances' => [],
                ];
            }

            $groupsByDoctor[$doctorId]['appointments'][$appointmentId]['ordonnances'][] = $ordonnance;
        }

        $filteredGroups = [];
        foreach ($groupsByDoctor as $group) {
            $appointmentsList = array_values(array_filter(
                $group['appointments'],
                static fn (array $entry): bool =>
                    !empty($entry['rapports']) || !empty($entry['ordonnances'])
            ));

            if ($appointmentsList === []) {
                continue;
            }

            usort(
                $appointmentsList,
                static fn (array $a, array $b) =>
                    self::appointmentTimestamp($b['appointment']) <=> self::appointmentTimestamp($a['appointment'])
            );
            $group['appointments'] = $appointmentsList;
            $filteredGroups[] = $group;
        }

        $doctorGroups = array_values($filteredGroups);

        return $this->render('patient_dashboard/reports_prescriptions.html.twig', [
            'doctorGroups' => $doctorGroups,
        ]);
    }

    private static function appointmentTimestamp(Appointment $appointment): int
    {
        $date = $appointment->getDate();
        $time = $appointment->getStartTime();
        if (!$date || !$time) {
            return 0;
        }

        $dateTime = (new \DateTimeImmutable($date->format('Y-m-d 00:00:00')))
            ->setTime((int) $time->format('H'), (int) $time->format('i'));

        return $dateTime->getTimestamp();
    }

    private function safeGetAppointmentFromRapport(EntityManagerInterface $entityManager, Rapport $rapport): ?Appointment
    {
        try {
            return $this->resolveAppointment($entityManager, $rapport->getAppointment());
        } catch (EntityNotFoundException) {
            return null;
        }
    }

    private function safeGetAppointmentFromOrdonnance(EntityManagerInterface $entityManager, Ordonnance $ordonnance): ?Appointment
    {
        try {
            return $this->resolveAppointment($entityManager, $ordonnance->getAppointment());
        } catch (EntityNotFoundException) {
            return null;
        }
    }

    private function resolveAppointment(EntityManagerInterface $entityManager, ?Appointment $appointment): ?Appointment
    {
        if (!$appointment instanceof Appointment) {
            return null;
        }

        try {
            $id = $appointment->getId();
        } catch (EntityNotFoundException) {
            return null;
        }

        if (!$id) {
            return null;
        }

        $resolved = $entityManager->getRepository(Appointment::class)->find($id);
        if (!$resolved instanceof Appointment) {
            return null;
        }

        try {
            $resolved->getDate();
            $resolved->getStartTime();
        } catch (EntityNotFoundException) {
            return null;
        }

        return $resolved;
    }
}
