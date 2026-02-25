<?php

namespace App\Controller;

use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\Appointment;
use App\Repository\MedecinRepository;
use App\Repository\TempsTravailRepository;
use App\Repository\IndisponibiliteRepository;
use App\Repository\CalendarSettingRepository;
use App\Repository\AppointmentRepository;
use App\Repository\FeedbackRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BookingController extends AbstractController
{
    private EmailService $emailService;
    private MedecinRepository $medecinRepo;

    public function __construct(EmailService $emailService, MedecinRepository $medecinRepo)
    {
        $this->emailService = $emailService;
        $this->medecinRepo = $medecinRepo;
    }

    #[Route('/booking/doctor/{id}', name: 'app_booking_doctor')]
    public function bookingDoctor(Medecin $medecin): Response
    {
        return $this->render('pages/booking.html.twig', [
            'medecin' => $medecin,
        ]);
    }

    #[Route('/api/booking/availability/{id}', name: 'app_api_booking_availability', methods: ['GET'])]
    public function getAvailability(
        int $id,
        TempsTravailRepository $ttRepo,
        IndisponibiliteRepository $indispRepo,
        CalendarSettingRepository $settingsRepo,
        AppointmentRepository $apptRepo
    ): JsonResponse {
        $specifics = $ttRepo->createQueryBuilder('tt')
            ->where('tt.specificDate IS NOT NULL')
            ->andWhere('tt.doctorId = :doctorId')
            ->setParameter('doctorId', $id)
            ->getQuery()
            ->getResult();

        $indisps = $indispRepo->findBy(['doctorId' => $id]);
        $appts = $apptRepo->findBy(['doctor' => $id]);
        $settings = $settingsRepo->findOneBy(['doctorId' => $id]);

        if (!$settings) {
            $settingsData = [
                'slotDuration' => 30,
                'pauseStart' => '12:00',
                'pauseEnd' => '14:00'
            ];
        } else {
            $settingsData = [
                'slotDuration' => $settings->getSlotDuration(),
                'pauseStart' => $settings->getPauseStart() ? $settings->getPauseStart()->format('H:i') : '12:00',
                'pauseEnd' => $settings->getPauseEnd() ? $settings->getPauseEnd()->format('H:i') : '14:00'
            ];
        }

        $formatTT = function ($tt) {
            return [
                'id' => $tt->getId(),
                'dayOfWeek' => $tt->getDayOfWeek(),
                'startTime' => $tt->getStartTime()->format('H:i'),
                'endTime' => $tt->getEndTime()->format('H:i'),
                'doctorId' => $tt->getDoctorId(),
                'specificDate' => $tt->getSpecificDate() ? $tt->getSpecificDate()->format('Y-m-d') : null,
            ];
        };

        return new JsonResponse([
            'settings' => $settingsData,
            'specifics' => array_map($formatTT, $specifics),
            'indisponibilities' => array_map(function ($i) {
                return [
                    'date' => $i->getDate()->format('Y-m-d'),
                    'isEmergency' => $i->isEmergency(),
                ];
            }, $indisps),
            'appointments' => array_map(function ($a) {
                return [
                    'id' => $a->getId(),
                    'date' => $a->getDate()->format('Y-m-d'),
                    'startTime' => $a->getStartTime()->format('H:i'),
                    'duration' => $a->getDuration(),
                    'status' => $a->getStatus(),
                ];
            }, $appts),
        ]);
    }

    #[Route('/api/booking/save', name: 'app_api_booking_save', methods: ['POST'])]
    public function saveBooking(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        $user = $this->getUser();
        if (!$user || !($user instanceof Patient)) {
            return new JsonResponse(['error' => 'Veuillez vous connecter pour prendre rendez-vous.'], 403);
        }

        $doctorId = (int)$data['doctorId'];
        $doctor = $this->medecinRepo->find($doctorId);
        if (!$doctor) {
            return new JsonResponse(['error' => 'Médecin non trouvé.'], 404);
        }

        $appointment = new Appointment();
        $appointment->setPatient($user);
        $appointment->setDoctor($doctor);
        $appointment->setDate(new \DateTime($data['date']));
        $appointment->setStartTime(new \DateTime($data['startTime']));
        $appointment->setDuration((int)($data['duration'] ?? 30));
        $appointment->setStatus('scheduled');

        $em->persist($appointment);
        $em->flush();

        // Send confirmation email
        try {
            $this->emailService->sendAppointmentConfirmation($appointment, $user, $doctor);
        } catch (\Exception $e) {
            // Log error but don't fail the booking
        }

        return new JsonResponse(['success' => true]);
    }

    #[Route('/my-appointments', name: 'app_patient_appointment_list')]
    public function myAppointments(Request $request, AppointmentRepository $apptRepo, FeedbackRepository $feedbackRepo): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $search = $request->query->get('search');
        $sortBy = $request->query->get('sortBy', 'date');
        $order = $request->query->get('order', 'DESC');

        $qb = $apptRepo->createQueryBuilder('a')
            ->where('a.patient = :patient')
            ->setParameter('patient', $user);

        if ($sortBy === 'status') {
            $qb->orderBy('a.status', $order);
        } else {
            $qb->orderBy('a.date', $order)
               ->addOrderBy('a.startTime', $order);
        }

        $appts = $qb->getQuery()->getResult();
        
        $enhancedAppts = [];
        foreach ($appts as $appt) {
            $doctor = $appt->getDoctor();
            $doctorName = $doctor ? $doctor->getFirstName() . ' ' . $doctor->getLastName() : 'Inconnu';

            // Filter by doctor name if search is provided
            if ($search && stripos($doctorName, $search) === false) {
                continue;
            }

            $feedback = $feedbackRepo->findOneBy(['appointment' => $appt]);
            
            $enhancedAppts[] = [
                'id' => $appt->getId(),
                'date' => $appt->getDate(),
                'startTime' => $appt->getStartTime(),
                'duration' => $appt->getDuration(),
                'status' => $appt->getStatus(),
                'doctor' => $doctor,
                'doctorName' => $doctorName,
                'feedbackId' => $feedback ? $feedback->getId() : null
            ];
        }

        return $this->render('pages/my_appointments.html.twig', [
            'appointments' => $enhancedAppts,
            'currentSearch' => $search,
            'currentSortBy' => $sortBy,
            'currentOrder' => $order
        ]);
    }

    #[Route('/api/booking/cancel/{id}', name: 'app_api_booking_cancel', methods: ['POST'])]
    public function cancelAppointment(int $id, AppointmentRepository $apptRepo, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user || !($user instanceof Patient)) {
            return new JsonResponse(['error' => 'Veuillez vous connecter.'], 403);
        }

        $appointment = $apptRepo->find($id);
        if (!$appointment || $appointment->getPatient() !== $user) {
            return new JsonResponse(['error' => 'Rendez-vous non trouvé.'], 404);
        }

        if ($appointment->getStatus() !== 'scheduled') {
            return new JsonResponse(['error' => 'Seuls les rendez-vous planifiés peuvent être annulés.'], 400);
        }

        $doctor = $appointment->getDoctor();

        $appointment->setStatus('cancelled');
        $em->flush();

        // Send cancellation email
        if ($doctor) {
            try {
                $this->emailService->sendAppointmentCancellation($appointment, $user, $doctor);
            } catch (\Exception $e) {
                // Log error
            }
        }

        return new JsonResponse(['success' => true]);
    }
}