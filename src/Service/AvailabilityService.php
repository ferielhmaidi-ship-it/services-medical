<?php

namespace App\Service;

use App\Entity\Medecin;
use App\Entity\Appointment;
use App\Repository\IndisponibiliteRepository;
use App\Repository\TempsTravailRepository;
use App\Repository\AppointmentRepository;
use Psr\Log\LoggerInterface;

class AvailabilityService
{
    private TempsTravailRepository $ttRepo;
    private IndisponibiliteRepository $indispRepo;
    private AppointmentRepository $apptRepo;
    private LoggerInterface $logger;

    public function __construct(
        TempsTravailRepository $ttRepo,
        IndisponibiliteRepository $indispRepo,
        AppointmentRepository $apptRepo,
        LoggerInterface $logger
        )
    {
        $this->ttRepo = $ttRepo;
        $this->indispRepo = $indispRepo;
        $this->apptRepo = $apptRepo;
        $this->logger = $logger;
    }

    /**
     * Get the list of formatted dates for the current week where the doctor is working.
     */
    public function getWeeklyWorkingDays(Medecin $doctor, \DateTimeInterface $startDate): array
    {
        $workingDays = [];
        $doctorId = $doctor->getId();

        $endDate = (clone $startDate)->modify('+7 days');
        $currentDate = clone $startDate;

        while ($currentDate < $endDate) {
            $hours = $this->getWorkingHoursForDayOptimized($this->ttRepo->findBy(['doctorId' => $doctorId]), $currentDate);
            if (!empty($hours)) {
                if (!$this->checkUnavailableBulk($this->indispRepo->findBy(['doctorId' => $doctorId]), $currentDate)) {
                    $workingDays[] = $this->formatFrenchDate($currentDate, 'EEE : d MMM');
                }
            }
            $currentDate->modify('+1 day');
        }

        return $workingDays;
    }

    /**
     * Main logic to get all free slots for a window of $daysCount
     */
    public function getAvailableSlots(Medecin $doctor, \DateTimeInterface $startDate, int $daysCount = 14): array
    {
        $doctorId = $doctor->getId();
        $this->logger->info("AvailabilityService: FETCHING slots for Doctor $doctorId starting " . $startDate->format('Y-m-d') . " for $daysCount days.");

        $allTT = $this->ttRepo->findBy(['doctorId' => $doctorId]);
        $allIndisps = $this->indispRepo->findBy(['doctorId' => $doctorId]);

        $endDateLimit = (clone $startDate)->modify("+$daysCount days");
        $allAppts = $this->apptRepo->findByDoctorAndRange($doctor, $startDate, $endDateLimit);

        $availableSlotsByDay = [];

        for ($i = 0; $i < $daysCount; $i++) {
            $currentDate = (clone $startDate)->modify("+$i days");
            $currentDate->setTime(0, 0, 0);
            $dateStr = $currentDate->format('Y-m-d');

            // 1. Get working hours
            $workingHours = $this->getWorkingHoursForDayOptimized($allTT, $currentDate);
            if (empty($workingHours))
                continue;

            // 2. Check unavailability
            if ($this->checkUnavailableBulk($allIndisps, $currentDate))
                continue;

            // 3. Generate slots
            $daySlots = $this->generateSlotsForDay($currentDate, $workingHours, $allAppts);

            if (!empty($daySlots)) {
                $availableSlotsByDay[] = [
                    'date' => $dateStr,
                    'slots' => $daySlots
                ];
            }
        }

        $this->logger->info("AvailabilityService: Success. Found " . count($availableSlotsByDay) . " days with slots.");
        return $availableSlotsByDay;
    }

    /**
     * Fallback for empty windows: finds the next single available slot
     */
    public function getNextAvailableSlot(Medecin $doctor, \DateTimeInterface $startDate): ?array
    {
        for ($i = 0; $i < 45; $i++) {
            $day = (clone $startDate)->modify("+$i days");
            $slots = $this->getAvailableSlots($doctor, $day, 1);
            if (!empty($slots)) {
                return [
                    'date' => $slots[0]['date'],
                    'time' => $slots[0]['slots'][0]
                ];
            }
        }
        return null;
    }

    /**
     * Internal slot generator
     */
    private function generateSlotsForDay(\DateTimeInterface $date, array $workingHours, array $allAppts): array
    {
        $slots = [];
        $dateStr = $date->format('Y-m-d');
        $dayAppts = array_filter($allAppts, fn($a) => $a->getDate()->format('Y-m-d') === $dateStr);
        $now = new \DateTime();

        foreach ($workingHours as $wh) {
            $start = $wh['start'];
            $end = $wh['end'];
            $interval = 30;

            // TT stores start/end as DateTime objects (usually today + time)
            $current = (clone $date)->setTime($start->format('H'), $start->format('i'));
            $endOfDay = (clone $date)->setTime($end->format('H'), $end->format('i'));

            while ($current < $endOfDay) {
                // Skip past slots
                if ($current <= $now) {
                    $current->modify("+$interval minutes");
                    continue;
                }

                // Hardcoded pause for now (could be dynamic from settings later)
                $slotTimeStr = $current->format('H:i');
                if ($slotTimeStr >= '12:00' && $slotTimeStr < '14:00') {
                    $current->modify("+$interval minutes");
                    continue;
                }

                if ($this->isSlotFree($current, $dayAppts)) {
                    $slots[] = $current->format('H:i');
                }
                $current->modify("+$interval minutes");
            }
        }

        return array_unique($slots);
    }

    private function getWorkingHoursForDayOptimized(array $allTT, \DateTimeInterface $date): array
    {
        $dayNameEn = $date->format('l');
        $dateStr = $date->format('Y-m-d');

        $frenchDays = [
            'Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche'
        ];
        $dayNameFr = $frenchDays[$dayNameEn];

        $results = [];
        $specificFound = false;

        // 1. Specific Dates priority
        foreach ($allTT as $tt) {
            if ($tt->getSpecificDate() && $tt->getSpecificDate()->format('Y-m-d') === $dateStr) {
                $results[] = ['start' => $tt->getStartTime(), 'end' => $tt->getEndTime()];
                $specificFound = true;
            }
        }
        if ($specificFound)
            return $results;

        // 2. Weekly Schedule
        foreach ($allTT as $tt) {
            if (!$tt->getSpecificDate() && $tt->getDayOfWeek()) {
                $dbDay = $tt->getDayOfWeek();
                if ($dbDay === $dayNameEn || $dbDay === $dayNameFr) {
                    $results[] = ['start' => $tt->getStartTime(), 'end' => $tt->getEndTime()];
                }
            }
        }

        // 3. Fallback: only if doctor has NO configuration at all
        if (empty($results)) {
            $hasAnyWeeklyConfig = false;
            foreach ($allTT as $tt) {
                if (!$tt->getSpecificDate()) {
                    $hasAnyWeeklyConfig = true;
                    break;
                }
            }

            if (!$hasAnyWeeklyConfig && !in_array($dayNameEn, ['Saturday', 'Sunday'])) {
                $fallbackStart = (new \DateTime())->setTime(9, 0);
                $fallbackEnd = (new \DateTime())->setTime(17, 0);
                return [['start' => $fallbackStart, 'end' => $fallbackEnd]];
            }
        }

        return $results;
    }

    private function checkUnavailableBulk(array $allIndisp, \DateTimeInterface $date): bool
    {
        $dateStr = $date->format('Y-m-d');
        foreach ($allIndisp as $i) {
            if ($i->getDate()->format('Y-m-d') === $dateStr)
                return true;
        }
        return false;
    }

    private function isSlotFree(\DateTimeInterface $slotTime, array $dayAppts): bool
    {
        $slotStr = $slotTime->format('H:i');
        foreach ($dayAppts as $appt) {
            // Check status - usually only active appointments block slots
            if (in_array($appt->getStatus(), ['cancelled', 'annule'])) {
                continue;
            }

            // Check if slot matches appointment time
            if ($appt->getStartTime() && $appt->getStartTime()->format('H:i') === $slotStr) {
                return false;
            }
            // Fallback for combined date field
            if ($appt->getDate() && $appt->getDate()->format('H:i') === $slotStr) {
                return false;
            }
        }
        return true;
    }

    private function formatFrenchDate(\DateTimeInterface $date, string $pattern): string
    {
        $days = [
            'Monday' => 'Lun', 'Tuesday' => 'Mar', 'Wednesday' => 'Mer',
            'Thursday' => 'Jeu', 'Friday' => 'Ven', 'Saturday' => 'Sam', 'Sunday' => 'Dim'
        ];
        $shortMonths = [
            'Jan' => 'janv.', 'Feb' => 'févr.', 'Mar' => 'mars', 'Apr' => 'avr.',
            'May' => 'mai', 'Jun' => 'juin', 'Jul' => 'juil.', 'Aug' => 'août',
            'Sep' => 'sept.', 'Oct' => 'oct.', 'Nov' => 'nov.', 'Dec' => 'déc.'
        ];

        if ($pattern === 'EEE : d MMM') {
            return ($days[$date->format('l')] ?? $date->format('D')) . ' : ' . $date->format('d') . ' ' . ($shortMonths[$date->format('M')] ?? $date->format('M'));
        }

        return $date->format('d/m/Y');
    }
}
