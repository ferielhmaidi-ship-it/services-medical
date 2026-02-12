<?php

namespace App\Service;

use App\Entity\Medecin;
use App\Repository\AppointmentRepository;
use App\Repository\CalendarSettingRepository;
use App\Repository\IndisponibiliteRepository;
use App\Repository\TempsTravailRepository;

class AvailabilityService
{
    public function __construct(private
        TempsTravailRepository $ttRepo, private
        IndisponibiliteRepository $indispRepo, private
        CalendarSettingRepository $settingsRepo, private
        AppointmentRepository $apptRepo
        )
    {
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

        file_put_contents('c:/Users/PC/OneDrive/Desktop/test1/availability_trace.txt', "START_DATE: " . $startDate->format('Y-m-d') . " | DOCTOR: " . $doctorId . "\n", FILE_APPEND);
        while ($currentDate < $endDate) {
            $hours = $this->getWorkingHoursForDay($doctorId, $currentDate);
            $msg = "Checking Doc $doctorId | " . $currentDate->format('Y-m-d') . ": ";

            if (!empty($hours)) {
                $msg .= "Working hours found. ";
                if (!$this->isUnavailable($doctorId, $currentDate)) {
                    $workingDays[] = $this->formatFrenchDate($currentDate, 'EEE : d MMM');
                    $msg .= "Added.";
                }
                else {
                    $msg .= "Blocked by Indisponibilite.";
                }
            }
            else {
                $msg .= "No working hours found.";
            }
            file_put_contents('c:/Users/PC/OneDrive/Desktop/test1/availability_trace.txt', $msg . "\n", FILE_APPEND);
            $currentDate->modify('+1 day');
        }

        return $workingDays;
    }

    /**
     * Find the next available slot for a doctor.
     */
    public function getNextAvailableSlot(Medecin $doctor, \DateTimeInterface $from): ?array
    {
        $doctorId = $doctor->getId();
        $settings = $this->settingsRepo->findOneBy(['doctorId' => $doctorId]);

        $slotDuration = $settings ? $settings->getSlotDuration() : 30;
        $pauseStart = $settings && $settings->getPauseStart() ? $settings->getPauseStart()->format('H:i') : '12:00';
        $pauseEnd = $settings && $settings->getPauseEnd() ? $settings->getPauseEnd()->format('H:i') : '14:00';

        // Check the next 30 days
        $currentDate = clone $from;
        $limitDate = (clone $from)->modify('+30 days');

        while ($currentDate < $limitDate) {
            $dayWorkingHours = $this->getWorkingHoursForDay($doctorId, $currentDate);

            if ($dayWorkingHours) {
                foreach ($dayWorkingHours as $hours) {
                    $startTime = \DateTime::createFromFormat('Y-m-d H:i', $currentDate->format('Y-m-d') . ' ' . $hours['start']);
                    $endTime = \DateTime::createFromFormat('Y-m-d H:i', $currentDate->format('Y-m-d') . ' ' . $hours['end']);

                    // If searching on the starting day, start from the 'from' time if it's after startTime
                    if ($currentDate->format('Y-m-d') === $from->format('Y-m-d')) {
                        if ($from > $startTime) {
                            $startTime = clone $from;
                            // Align to slot duration
                            $minutes = (int)$startTime->format('i');
                            $remainder = $minutes % $slotDuration;
                            if ($remainder !== 0) {
                                $startTime->modify('+' . ($slotDuration - $remainder) . ' minutes');
                            }
                            $startTime->setTime((int)$startTime->format('H'), (int)$startTime->format('i'), 0);
                        }
                    }

                    $slot = clone $startTime;
                    while ($slot < $endTime) {
                        $slotEnd = (clone $slot)->modify('+' . $slotDuration . ' minutes');

                        // Check if slot overlaps with pause
                        $slotTimeStr = $slot->format('H:i');
                        if ($slotTimeStr >= $pauseStart && $slotTimeStr < $pauseEnd) {
                            $slot->modify('+' . $slotDuration . ' minutes');
                            continue;
                        }

                        // Check if doctor is unavailable (indisponibilite)
                        if ($this->isUnavailable($doctorId, $slot)) {
                            break; // Skip rest of day if it's an emergency/full day indisp
                        }

                        // Check if slot is already booked
                        if (!$this->isBooked($doctorId, $slot, $slotDuration)) {
                            return [
                                'date' => $this->formatFrenchDate($slot, 'd F Y'),
                                'time' => $slot->format('H:i')
                            ];
                        }

                        $slot->modify('+' . $slotDuration . ' minutes');
                    }
                }
            }

            $currentDate->modify('+1 day');
            // Reset hours for the new day
            $currentDate->setTime(0, 0);
        }

        return null;
    }

    private function formatFrenchDate(\DateTimeInterface $date, string $pattern): string
    {
        $days = [
            'Monday' => 'Lun', 'Tuesday' => 'Mar', 'Wednesday' => 'Mer',
            'Thursday' => 'Jeu', 'Friday' => 'Ven', 'Saturday' => 'Sam', 'Sunday' => 'Dim'
        ];
        $months = [
            'January' => 'janvier', 'February' => 'février', 'March' => 'mars',
            'April' => 'avril', 'May' => 'mai', 'June' => 'juin',
            'July' => 'juillet', 'August' => 'août', 'September' => 'septembre',
            'October' => 'octobre', 'November' => 'novembre', 'December' => 'décembre'
        ];
        $shortMonths = [
            'Jan' => 'janv.', 'Feb' => 'févr.', 'Mar' => 'mars', 'Apr' => 'avr.',
            'May' => 'mai', 'Jun' => 'juin', 'Jul' => 'juil.', 'Aug' => 'août',
            'Sep' => 'sept.', 'Oct' => 'oct.', 'Nov' => 'nov.', 'Dec' => 'déc.'
        ];

        if ($pattern === 'EEE : d MMM') {
            return $days[$date->format('l')] . ' : ' . $date->format('d') . ' ' . $shortMonths[$date->format('M')];
        }

        if ($pattern === 'd F Y') {
            return $date->format('d') . ' ' . $months[$date->format('F')] . ' ' . $date->format('Y');
        }

        return $date->format('d/m/Y');
    }

    private function getWorkingHoursForDay(int $doctorId, \DateTimeInterface $date): array
    {
        $dateStr = $date->format('Y-m-d');
        $dayName = strtolower(trim($date->format('l')));

        // Fetch all to handle correctly in PHP (more robust against case/whitespace/nulls in DB)
        $allTT = $this->ttRepo->findBy(['doctorId' => $doctorId]);

        $result = [];
        $specificFound = false;

        // 1. Check for specific date first
        foreach ($allTT as $tt) {
            if ($tt->getSpecificDate() && $tt->getSpecificDate()->format('Y-m-d') === $dateStr) {
                $result[] = [
                    'start' => $tt->getStartTime()->format('H:i'),
                    'end' => $tt->getEndTime()->format('H:i')
                ];
                $specificFound = true;
            }
        }

        if ($specificFound) {
            return $result;
        }

        // 2. Check regular weekly hours
        foreach ($allTT as $tt) {
            if (!$tt->getSpecificDate() && $tt->getDayOfWeek()) {
                if (strtolower(trim($tt->getDayOfWeek())) === $dayName) {
                    $result[] = [
                        'start' => $tt->getStartTime()->format('H:i'),
                        'end' => $tt->getEndTime()->format('H:i')
                    ];
                }
            }
        }

        return $result;
    }

    private function isUnavailable(int $doctorId, \DateTimeInterface $date): bool
    {
        $dateOnly = \DateTime::createFromFormat('Y-m-d', $date->format('Y-m-d'));
        $dateOnly->setTime(0, 0, 0);

        $indisp = $this->indispRepo->findOneBy([
            'doctorId' => $doctorId,
            'date' => $dateOnly
        ]);
        return $indisp !== null;
    }

    private function isBooked(int $doctorId, \DateTimeInterface $slot, int $slotDuration): bool
    {
        $slotStart = clone $slot;
        $slotEnd = (clone $slot)->modify('+' . $slotDuration . ' minutes');

        // Check for any appointment that overlaps with the slot
        // Overlap condition: appointment_start < slot_end AND slot_start < appointment_end
        // appointment_end = appointment_start + appointment_duration

        $qb = $this->apptRepo->createQueryBuilder('a')
            ->where('a.doctorId = :doctorId')
            ->andWhere('a.date = :date')
            ->andWhere('a.status = :status')
            ->setParameter('doctorId', $doctorId)
            ->setParameter('date', $slotStart->format('Y-m-d'))
            ->setParameter('status', 'scheduled');

        $appointments = $qb->getQuery()->getResult();

        foreach ($appointments as $appt) {
            $apptStart = clone $appt->getStartTime();
            $apptStart->setDate((int)$slotStart->format('Y'), (int)$slotStart->format('m'), (int)$slotStart->format('d'));

            $apptEnd = (clone $apptStart)->modify('+' . $appt->getDuration() . ' minutes');

            if ($apptStart < $slotEnd && $slotStart < $apptEnd) {
                return true;
            }
        }

        return false;
    }
}
