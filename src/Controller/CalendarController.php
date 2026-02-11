<?php

namespace App\Controller;

use App\Entity\TempsTravail;
use App\Entity\Indisponibilite;
use App\Entity\CalendarSetting;
use App\Entity\Appointment;
use App\Repository\TempsTravailRepository;
use App\Repository\IndisponibiliteRepository;
use App\Repository\CalendarSettingRepository;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MEDECIN')]
class CalendarController extends AbstractController
{
    #[Route('/calendrier', name: 'app_calendrier')]
    public function index(): Response
    {
        return $this->render('pages/calendrier.html.twig');
    }

    #[Route('/api/calendar/config', name: 'app_api_calendar_config', methods: ['GET'])]
    public function getConfig(
        TempsTravailRepository $ttRepo, 
        IndisponibiliteRepository $indispRepo,
        CalendarSettingRepository $settingsRepo,
        AppointmentRepository $apptRepo
    ): JsonResponse
    {
        $doctorId = $this->getUser()->getId();

        $specifics = $ttRepo->createQueryBuilder('tt')
            ->where('tt.specificDate IS NOT NULL')
            ->andWhere('tt.doctorId = :doctorId')
            ->setParameter('doctorId', $doctorId)
            ->getQuery()
            ->getResult();
            
        $indisps = $indispRepo->findBy(['doctorId' => $doctorId]);
        
        $appts = $apptRepo->findBy(['doctorId' => $doctorId]); // Appointment uses doctorId column

        $settings = $settingsRepo->findOneBy(['doctorId' => $doctorId]);
        if (!$settings) {
            $settings = [
                'slotDuration' => 30,
                'pauseStart' => '12:00',
                'pauseEnd' => '14:00'
            ];
        } else {
            $settings = [
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
            'settings' => $settings,
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
                    'patientId' => $a->getPatientId(),
                    'date' => $a->getDate()->format('Y-m-d'),
                    'startTime' => $a->getStartTime()->format('H:i'),
                    'duration' => $a->getDuration(),
                    'status' => $a->getStatus(),
                ];
            }, $appts),
        ]);
    }

    #[Route('/api/calendar/settings', name: 'app_api_calendar_settings_save', methods: ['POST'])]
    public function saveSettings(Request $request, EntityManagerInterface $em, CalendarSettingRepository $settingsRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) return new JsonResponse(['error' => 'Invalid data'], 400);

        $doctorId = $this->getUser()->getId();
        $settings = $settingsRepo->findOneBy(['doctorId' => $doctorId]) ?: new CalendarSetting();
        $settings->setDoctorId($doctorId);
        $settings->setSlotDuration((int)($data['slotDuration'] ?? 30));
        
        if (isset($data['pauseStart'])) $settings->setPauseStart(new \DateTime($data['pauseStart']));
        if (isset($data['pauseEnd'])) $settings->setPauseEnd(new \DateTime($data['pauseEnd']));

        $em->persist($settings);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/calendar/next-week', name: 'app_calendar_next_week', methods: ['POST'])]
    public function saveNextWeek(Request $request, EntityManagerInterface $em, TempsTravailRepository $ttRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['weekDates'])) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        $allWeekDates = $data['weekDates'];
        sort($allWeekDates);
        $minDate = new \DateTime($allWeekDates[0]);
        $maxDate = new \DateTime(end($allWeekDates));

        $doctorId = $this->getUser()->getId();

        $existingInRange = $ttRepo->createQueryBuilder('tt')
            ->where('tt.specificDate >= :min')
            ->andWhere('tt.specificDate <= :max')
            ->andWhere('tt.doctorId = :doctorId')
            ->setParameter('min', $minDate->format('Y-m-d'))
            ->setParameter('max', $maxDate->format('Y-m-d'))
            ->setParameter('doctorId', $doctorId)
            ->getQuery()
            ->getResult();
        
        foreach ($existingInRange as $old) {
            $em->remove($old);
        }
        $em->flush(); 

        if (isset($data['days']) && is_array($data['days'])) {
            foreach ($data['days'] as $dayData) {
                $date = new \DateTime($dayData['date']);
                $tt = new TempsTravail();
                $tt->setSpecificDate($date);
                $tt->setDayOfWeek($date->format('l'));
                $tt->setStartTime(new \DateTime($dayData['startTime']));
                $tt->setEndTime(new \DateTime($dayData['endTime']));
                $tt->setDoctorId($doctorId); 
                $em->persist($tt);
            }
            $em->flush();
        }

        return new JsonResponse(['success' => true]);
    }

    #[Route('/calendar/emergency', name: 'app_calendar_emergency', methods: ['POST'])]
    public function saveEmergency(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['date'])) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        $date = new \DateTime($data['date']);
        $indisp = new Indisponibilite();
        $indisp->setDate($date);
        $indisp->setIsEmergency(true);
        $indisp->setDoctorId($this->getUser()->getId());

        $em->persist($indisp);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }
}