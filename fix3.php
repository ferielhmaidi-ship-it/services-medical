<?php

function replaceLines($path, $replacements)
{
    if (!file_exists($path)) {
        echo "File not found: $path\n";
        return;
    }
    $lines = file($path);
    $changed = false;
    foreach ($lines as $i => $line) {
        $trimmed = trim($line);
        foreach ($replacements as $search => $replaceConf) {
            if ($trimmed === $search) {
                // Determine leading whitespace
                preg_match('/^\s*/', $line, $matches);
                $indent = $matches[0];

                if (is_array($replaceConf)) {
                    $replacementLine = "";
                    foreach ($replaceConf as $rLine) {
                        $replacementLine .= $indent . $rLine . "\n";
                    }
                    $lines[$i] = rtrim($replacementLine, "\n") . "\n"; // keep original newline logic roughly
                }
                else {
                    $lines[$i] = $indent . $replaceConf . "\n";
                }
                $changed = true;
                break;
            }
        }
    }
    if ($changed) {
        file_put_contents($path, implode("", $lines));
        echo "Updated $path\n";
    }
}

// 1. AdminMedecinController
replaceLines(__DIR__ . '/src/Controller/AdminMedecinController.php', [
    'private DoctorVerificationService $doctorVerificationService;' => '',
    'public function __construct(UserPasswordHasherInterface $passwordHasher, DoctorVerificationService $doctorVerificationService)' => 'public function __construct(UserPasswordHasherInterface $passwordHasher)',
    '$this->doctorVerificationService = $doctorVerificationService;' => ''
]);

// 2. BookingController
replaceLines(__DIR__ . '/src/Controller/BookingController.php', [
    '$isPatient = ($user instanceof Patient);' => '',
    'if ($isPatient && $user instanceof Patient) {' => 'if ($user instanceof Patient) {',
    'if ($isPatient) {' => 'if ($user instanceof Patient) {'
]);

// 3. CalendarController
replaceLines(__DIR__ . '/src/Controller/CalendarController.php', [
    'private PatientRepository $patientRepo;' => '',
    'public function __construct(PatientRepository $patientRepo)' => 'public function __construct()',
    '$this->patientRepo = $patientRepo;' => '',
    '$doctorId = $this->getUser()->getId();' => [
        '/** @var \App\Entity\Medecin $user */',
        '$user = $this->getUser();',
        '$doctorId = $user->getId();'
    ],
    '$indisp->setDoctorId($this->getUser()->getId());' => [
        '/** @var \App\Entity\Medecin $user */',
        '$user = $this->getUser();',
        '$indisp->setDoctorId($user->getId());'
    ],
    '$candidateRendezVous = $rendezVousRepository->createQueryBuilder(\'rv\')' => '$candidateRendezVous = $appointmentRepository->createQueryBuilder(\'rv\')',
    '->andWhere(\'rv.appointmentDate BETWEEN :dayStart AND :dayEnd\')' => '->andWhere(\'rv.date BETWEEN :dayStart AND :dayEnd\')'
]);

// 4. CreateAdminCommand
replaceLines(__DIR__ . '/src/Command/CreateAdminCommand.php', [
    '$answer = $this->getHelper(\'question\')->ask($input, $output, $question);' => [
        '/** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */',
        '$helper = $this->getHelper(\'question\');',
        '$answer = $helper->ask($input, $output, $question);'
    ]
]);

// 5. DocumentPdfController
replaceLines(__DIR__ . '/src/Controller/DocumentPdfController.php', [
    '$html = $this->renderView(\'pdf/rapport.html.twig\', [' => [
        '$patient = $rapport->getPatient();',
        '$medecin = $rapport->getMedecin();',
        '$html = $this->renderView(\'pdf/rapport.html.twig\', ['
    ],
    '\'rapport\' => $rapport,' => '\'rapport\' => $rapport,'
]);

// 6. IaController
replaceLines(__DIR__ . '/src/Controller/IaController.php', [
    'private string $uploadDirectory' => ''
]);

// 7. PatientDashboardController
replaceLines(__DIR__ . '/src/Controller/PatientDashboardController.php', [
    '$appointmentsList = array_values(array_filter(' => '$appointmentsList = array_filter(',
    '));' => '); // array_values removed'
]);

// 8. TestEmailController
replaceLines(__DIR__ . '/src/Controller/TestEmailController.php', [
    '$emailService->sendAppointmentConfirmation($appointment);' => [
        '$patient = $appointment->getPatient();',
        '$doctor = $appointment->getDoctor();',
        '$emailService->sendAppointmentConfirmation($appointment, $patient, $doctor);'
    ],
    '$emailService->sendAppointmentReminder($appointment);' => '$emailService->sendAppointmentReminder($appointment, $patient, $doctor);',
    '$emailService->sendAppointmentCancellation($appointment);' => '$emailService->sendAppointmentCancellation($appointment, $patient, $doctor);'
]);

// 9. Question.php
replaceLines(__DIR__ . '/src/Entity/Question.php', [
    'return $this->likedBy;' => [
        'return new \Doctrine\Common\Collections\ArrayCollection(array_merge(',
        '    $this->likedByPatients->toArray(),',
        '    $this->likedByMedecins->toArray()',
        '));'
    ]
]);

// 10. Reponse.php
replaceLines(__DIR__ . '/src/Entity/Reponse.php', [
    'return $this->likedBy;' => [
        'return new \Doctrine\Common\Collections\ArrayCollection(array_merge(',
        '    $this->likedByPatients->toArray(),',
        '    $this->likedByMedecins->toArray()',
        '));'
    ]
]);

// 11. AvailabilityService.php
replaceLines(__DIR__ . '/src/Service/AvailabilityService.php', [
    '$endDate = (clone $startDate)->modify(\'+7 days\');' => [
        '$currentDate = \DateTime::createFromInterface($startDate);',
        '$endDate = (clone $currentDate)->modify(\'+7 days\');'
    ],
    '$currentDate = clone $startDate;' => '', // removed
    '$endDateLimit = (clone $startDate)->modify("+$daysCount days");' => [
        '$startDateTime = \DateTime::createFromInterface($startDate);',
        '$endDateLimit = (clone $startDateTime)->modify("+$daysCount days");'
    ],
    '$allAppts = $this->apptRepo->findByDoctorAndRange($doctor, $startDate, $endDateLimit);' => '$allAppts = $this->apptRepo->findByDoctorAndRange($doctor, $startDateTime, $endDateLimit);',
    '$currentDate = (clone $startDate)->modify("+$i days");' => '$currentDate = (clone $startDateTime)->modify("+$i days");',
    '$day = (clone $startDate)->modify("+$i days");' => [
        '$startDateTime = \DateTime::createFromInterface($startDate);',
        '$day = (clone $startDateTime)->modify("+$i days");'
    ],
    '$current = (clone $date)->setTime((int)$start->format(\'H\'), (int)$start->format(\'i\'));' => '$current = \DateTime::createFromInterface($date)->setTime((int)$start->format(\'H\'), (int)$start->format(\'i\'), 0);',
    '$current = (clone $date)->setTime((int) $start->format(\'H\'), (int) $start->format(\'i\'));' => '$current = \DateTime::createFromInterface($date)->setTime((int)$start->format(\'H\'), (int)$start->format(\'i\'), 0);',
    '$endOfDay = (clone $date)->setTime((int)$end->format(\'H\'), (int)$end->format(\'i\'));' => '$endOfDay = \DateTime::createFromInterface($date)->setTime((int)$end->format(\'H\'), (int)$end->format(\'i\'), 0);',
    '$endOfDay = (clone $date)->setTime((int) $end->format(\'H\'), (int) $end->format(\'i\'));' => '$endOfDay = \DateTime::createFromInterface($date)->setTime((int)$end->format(\'H\'), (int)$end->format(\'i\'), 0);'
]);

echo "Line replace complete.\n";
