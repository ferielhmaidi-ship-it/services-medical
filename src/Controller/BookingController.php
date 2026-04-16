<?php

namespace App\Controller;

use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\Appointment;
use App\Repository\CalendarSettingRepository;
use App\Repository\IndisponibiliteRepository;
use App\Repository\MedecinRepository;
use App\Repository\AppointmentRepository;
use App\Repository\TempsTravailRepository;
use App\Service\AiSchedulingService;
use App\Service\AvailabilityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;

class BookingController extends AbstractController
{
    #[Route('/booking/doctor/{id}', name: 'app_booking_doctor')]
    public function index(Medecin $medecin): Response
    {
        return $this->render('pages/booking.html.twig', [
            'medecin' => $medecin,
        ]);
    }

    #[Route('/api/booking/availability/{id}', name: 'api_booking_availability', methods: ['GET'])]
    public function getAvailability(
        int $id,
        AvailabilityService $availabilityService,
        MedecinRepository $medecinRepo,
        AiSchedulingService $aiService,
        IndisponibiliteRepository $indispRepo,
        TempsTravailRepository $ttRepo,
        CalendarSettingRepository $settingsRepo,
        AppointmentRepository $apptRepo,
        LoggerInterface $logger
    ): JsonResponse {
        $medecin = $medecinRepo->find($id);
        if (!$medecin) {
            return new JsonResponse(['error' => 'Doctor not found'], 404);
        }

        $settings = $settingsRepo->findOneBy(['doctorId' => $id]);
        $allTT = $ttRepo->findBy(['doctorId' => $id]);
        $allIndisps = $indispRepo->findBy(['doctorId' => $id]);
        $allAppts = $apptRepo->findByDoctorAndRange($medecin, new \DateTime('today'), (new \DateTime('today'))->modify('+21 days'));

        $data = [
            'settings' => [
                'slotDuration' => $settings ? $settings->getSlotDuration() : 30,
                'pauseStart' => $settings && $settings->getPauseStart() ? $settings->getPauseStart()->format('H:i') : '12:00',
                'pauseEnd' => $settings && $settings->getPauseEnd() ? $settings->getPauseEnd()->format('H:i') : '14:00',
            ],
            'specifics' => array_map(function($tt) {
                return [
                    'specificDate' => $tt->getSpecificDate() ? $tt->getSpecificDate()->format('Y-m-d') : null,
                    'startTime' => $tt->getStartTime()->format('H:i'),
                    'endTime' => $tt->getEndTime()->format('H:i'),
                ];
            }, array_filter($allTT, fn($tt) => $tt->getSpecificDate() !== null)),
            'indisponibilities' => array_map(function($i) {
                return [
                    'date' => $i->getDate()->format('Y-m-d'),
                    'isEmergency' => true
                ];
            }, $allIndisps),
            'appointments' => array_map(function($a) {
                $date = $a->getDate();
                $startTime = $a->getStartTime();
                return [
                    'date' => $date ? $date->format('Y-m-d') : null,
                    'startTime' => $startTime ? $startTime->format('H:i') : ($date ? $date->format('H:i') : null)
                ];
            }, $allAppts),
            'holidays' => [],
        ];

        $user = $this->getUser();
        if ($user instanceof Patient) {
            $slotsForAi = $availabilityService->getAvailableSlots($medecin, new \DateTime('today'), 21);
            $closestSlot = null;

            if (empty($slotsForAi)) {
                $closestSlot = $availabilityService->getNextAvailableSlot($medecin, new \DateTime('today'));
                $logger->info("DEBUG: 21-day window empty for Doctor $id. Found fallback slot: " . ($closestSlot ? json_encode($closestSlot) : "none"));
            }

            $data['aiSuggestions'] = $aiService->getSmartSuggestions($user, $medecin, $slotsForAi, $closestSlot);
        } else {
            $data['aiSuggestions'] = [
                'recommendation' => null,
                'debug' => 'User not recognized as Patient'
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/booking/save', name: 'api_booking_save', methods: ['POST'])]
    public function save(
        Request $request,
        EntityManagerInterface $em,
        MedecinRepository $medecinRepo
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user || !($user instanceof Patient)) {
            return new JsonResponse(['error' => 'Veuillez vous connecter en tant que patient pour réserver.'], 403);
        }

        $params = json_decode($request->getContent(), true);
        $doctorId = $params['doctorId'] ?? null;
        $dateStr = $params['date'] ?? null;
        $startTimeStr = $params['startTime'] ?? null;

        if (!$doctorId || !$dateStr || !$startTimeStr) {
            return new JsonResponse(['error' => 'Données manquantes.'], 400);
        }

        $medecin = $medecinRepo->find($doctorId);
        if (!$medecin) {
            return new JsonResponse(['error' => 'Médecin introuvable.'], 404);
        }

        try {
            $appointment = new Appointment();
            $appointment->setPatient($user);
            $appointment->setDoctor($medecin);
            $appointment->setDate(new \DateTime($dateStr));
            $appointment->setStartTime(new \DateTime($startTimeStr));
            $appointment->setStatus('pending');

            $em->persist($appointment);
            $em->flush();

            return new JsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()], 500);
        }
    }
}