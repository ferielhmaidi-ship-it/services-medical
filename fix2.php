<?php
function pregReplaceInFile($path, $pattern, $replacement)
{
    if (!file_exists($path)) {
        echo "File not found: $path\n";
        return;
    }
    $content = file_get_contents($path);
    $newContent = preg_replace($pattern, $replacement, $content);
    if ($newContent !== null && $content !== $newContent) {
        file_put_contents($path, $newContent);
        echo "Updated $path\n";
    }
    else {
        echo "No changes or regex failed: $path\n";
    }
}

// 1. AdminMedecinController
pregReplaceInFile(__DIR__ . '/src/Controller/AdminMedecinController.php',
    '/private DoctorVerificationService \$doctorVerificationService;\s*public function __construct\(UserPasswordHasherInterface \$passwordHasher, DoctorVerificationService \$doctorVerificationService\)\s*\{\s*\$this->passwordHasher = \$passwordHasher;\s*\$this->doctorVerificationService = \$doctorVerificationService;\s*\}/s',
    "public function __construct(UserPasswordHasherInterface \$passwordHasher)\n    {\n        \$this->passwordHasher = \$passwordHasher;\n    }");

// 2. BookingController
pregReplaceInFile(__DIR__ . '/src/Controller/BookingController.php',
    '/\$isPatient = \(\$user instanceof Patient\);\s*if \(\$isPatient && \$user instanceof Patient\) \{/s',
    'if ($user instanceof Patient) {');

pregReplaceInFile(__DIR__ . '/src/Controller/BookingController.php',
    '/\$isPatient = \(\$user instanceof Patient\);\s*if \(\$isPatient\) \{/s',
    'if ($user instanceof Patient) {');

// 3. CalendarController
pregReplaceInFile(__DIR__ . '/src/Controller/CalendarController.php',
    '/private PatientRepository \$patientRepo;\s*public function __construct\(PatientRepository \$patientRepo\)\s*\{\s*\$this->patientRepo = \$patientRepo;\s*\}/s',
    '');

pregReplaceInFile(__DIR__ . '/src/Controller/CalendarController.php',
    '/\$doctorId = \$this->getUser\(\)->getId\(\);\s*\$doctor = \$this->getUser\(\);/s',
    "/** @var \App\Entity\Medecin \$user */\n        \$user = \$this->getUser();\n        \$doctorId = \$user->getId();\n        \$doctor = \$user;");

pregReplaceInFile(__DIR__ . '/src/Controller/CalendarController.php',
    '/\$doctorId = \$this->getUser\(\)->getId\(\);/s',
    "/** @var \App\Entity\Medecin \$user */\n        \$user = \$this->getUser();\n        \$doctorId = \$user->getId();");

pregReplaceInFile(__DIR__ . '/src/Controller/CalendarController.php',
    '/\$indisp->setDoctorId\(\$this->getUser\(\)->getId\(\)\);/s',
    "/** @var \App\Entity\Medecin \$user */\n        \$user = \$this->getUser();\n        \$indisp->setDoctorId(\$user->getId());");

pregReplaceInFile(__DIR__ . '/src/Controller/CalendarController.php',
    '/\$rendezVousRepository->createQueryBuilder\(\'rv\'\)\s*->where\(\'rv\.patient = :patient\'\)\s*->andWhere\(\'rv\.doctor = :doctor\'\)\s*->andWhere\(\'rv\.appointmentDate BETWEEN :dayStart AND :dayEnd\'\)/s',
    "\$appointmentRepository->createQueryBuilder('rv')\n            ->where('rv.patient = :patient')\n            ->andWhere('rv.doctor = :doctor')\n            ->andWhere('rv.date BETWEEN :dayStart AND :dayEnd')");

// 4. CreateAdminCommand
pregReplaceInFile(__DIR__ . '/src/Command/CreateAdminCommand.php',
    '/foreach \(\$questions as \$name => \$question\) \{\s*\$answer = \$this->getHelper\(\'question\'\)->ask\(\$input, \$output, \$question\);/s',
    "/** @var \Symfony\Component\Console\Helper\QuestionHelper \$helper */\n        \$helper = \$this->getHelper('question');\n        foreach (\$questions as \$name => \$question) {\n            \$answer = \$helper->ask(\$input, \$output, \$question);");

// 5. DocumentPdfController
pregReplaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', '/getIdrapport\(\)/', 'getId()');
pregReplaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', '/getIdpatient\(\)/', 'getPatient()');
pregReplaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', '/getIdmedecin\(\)/', 'getMedecin()');
pregReplaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', '/getIdordonnance\(\)/', 'getId()');
pregReplaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', '/getIddocument\(\)/', 'getId()');
pregReplaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', '/getDateordonnance\(\)/', 'getDateOrdonnance()');
pregReplaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', '/getPrenom\(\)/', 'getFirstName()');
pregReplaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', '/getNom\(\)/', 'getLastName()');
pregReplaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', '/strlen\(\$pdfContent\)/', '(string) strlen($pdfContent)');

// 6. IaController
pregReplaceInFile(__DIR__ . '/src/Controller/IaController.php',
    '/public function __construct\(\s*private string \$uploadDirectory\s*\)\s*\{\}/s',
    'public function __construct() {}');

pregReplaceInFile(__DIR__ . '/src/Controller/IaController.php',
    '/\$date = \$r->getAppointmentDate\(\)\?->format\(\'Y-m-d H:i\'\);\s*\$dbText \.= "- RDV #\{\$r->getId\(\)\} \| Date: \{\$date\}\\\\n";\s*\$dbText \.= "  Patient: \{\$r->getPatient\(\)->getFullName\(\)\}\\\\n";\s*\$dbText \.= "  Médecin: Dr \{\$r->getDoctor\(\)->getFullName\(\)\}\\\\n";\s*\$dbText \.= "  Statut: \{\$r->getStatut\(\)\}\\\\n\\\\n";/s',
    "\$dateStr = \$r->getDate()?->format('Y-m-d') . ' ' . \$r->getStartTime()?->format('H:i');\n            \$dbText .= \"- RDV #{\$r->getId()} | Date: {\$dateStr}\\n\";\n            \$dbText .= \"  Patient: {\$r->getPatient()->getFullName()}\\n\";\n            \$dbText .= \"  Médecin: Dr {\$r->getDoctor()->getFullName()}\\n\";\n            \$dbText .= \"  Statut: {\$r->getStatus()}\\n\\n\";");

// 7. PatientDashboardController
pregReplaceInFile(__DIR__ . '/src/Controller/PatientDashboardController.php', '/array_values\(array_filter\(/', 'array_filter(');
pregReplaceInFile(__DIR__ . '/src/Controller/PatientDashboardController.php', '/ordonnances\'\]\s*\)\);/s', "ordonnances']\n            );");

// 8. QuestionController
pregReplaceInFile(__DIR__ . '/src/Controller/QuestionController.php', '/if \(\$question->isLikedBy\(\$user\)\) \{/', "/** @var \App\Entity\BaseUser \$user */\n        if (\$question->isLikedBy(\$user)) {");
pregReplaceInFile(__DIR__ . '/src/Controller/QuestionController.php', '/\$question->removeLikedBy\(\$user\);/', "/** @var \App\Entity\BaseUser \$user */\n            \$question->removeLikedBy(\$user);");
pregReplaceInFile(__DIR__ . '/src/Controller/QuestionController.php', '/\$question->addLikedBy\(\$user\);/', "/** @var \App\Entity\BaseUser \$user */\n            \$question->addLikedBy(\$user);");

// 9. ReponseController
pregReplaceInFile(__DIR__ . '/src/Controller/ReponseController.php', '/if \(\$reponse->isLikedBy\(\$user\)\) \{/', "/** @var \App\Entity\BaseUser \$user */\n        if (\$reponse->isLikedBy(\$user)) {");
pregReplaceInFile(__DIR__ . '/src/Controller/ReponseController.php', '/\$reponse->removeLikedBy\(\$user\);/', "/** @var \App\Entity\BaseUser \$user */\n            \$reponse->removeLikedBy(\$user);");
pregReplaceInFile(__DIR__ . '/src/Controller/ReponseController.php', '/\$reponse->addLikedBy\(\$user\);/', "/** @var \App\Entity\BaseUser \$user */\n            \$reponse->addLikedBy(\$user);");

// 10. TestEmailController
pregReplaceInFile(__DIR__ . '/src/Controller/TestEmailController.php',
    '/\$emailService->sendAppointmentConfirmation\(\$appointment\);\s*\$confirmationResult = \'✅ Confirmation email sent successfully!<br>\';\s*\$emailService->sendAppointmentReminder\(\$appointment\);\s*\$reminderResult = \'✅ Reminder email sent successfully!<br>\';\s*\$emailService->sendAppointmentCancellation\(\$appointment\);\s*\$cancellationResult = \'✅ Cancellation email sent successfully!<br>\';/s',
    "\$patient = \$appointment->getPatient();\n            \$doctor = \$appointment->getDoctor();\n\n            \$emailService->sendAppointmentConfirmation(\$appointment, \$patient, \$doctor);\n            \$confirmationResult = '✅ Confirmation email sent successfully!<br>';\n            \n            \$emailService->sendAppointmentReminder(\$appointment, \$patient, \$doctor);\n            \$reminderResult = '✅ Reminder email sent successfully!<br>';\n\n            \$emailService->sendAppointmentCancellation(\$appointment, \$patient, \$doctor);\n            \$cancellationResult = '✅ Cancellation email sent successfully!<br>';");

// 11. Question.php
pregReplaceInFile(__DIR__ . '/src/Entity/Question.php',
    '/public function getLikedBy\(\): Collection\s*\{\s*return \$this->likedBy;\s*\}/s',
    "public function getLikedBy(): Collection\n    {\n        return new \Doctrine\Common\Collections\ArrayCollection(array_merge(\n            \$this->likedByPatients->toArray(),\n            \$this->likedByMedecins->toArray()\n        ));\n    }");

// 12. Reponse.php
pregReplaceInFile(__DIR__ . '/src/Entity/Reponse.php',
    '/public function getLikedBy\(\): Collection\s*\{\s*return \$this->likedBy;\s*\}/s',
    "public function getLikedBy(): Collection\n    {\n        return new \Doctrine\Common\Collections\ArrayCollection(array_merge(\n            \$this->likedByPatients->toArray(),\n            \$this->likedByMedecins->toArray()\n        ));\n    }");

// 13. AvailabilityService.php
pregReplaceInFile(__DIR__ . '/src/Service/AvailabilityService.php',
    '/\$endDate = \(clone \$startDate\)->modify\(\'\+7 days\'\);\s*\$currentDate = clone \$startDate;/s',
    "\$currentDate = \DateTime::createFromInterface(\$startDate);\n        \$endDate = (clone \$currentDate)->modify('+7 days');");

pregReplaceInFile(__DIR__ . '/src/Service/AvailabilityService.php',
    '/\$endDateLimit = \(clone \$startDate\)->modify\("\+\$daysCount days"\);\s*\$allAppts = \$this->apptRepo->findByDoctorAndRange\(\$doctor, \$startDate, \$endDateLimit\);/s',
    "\$startDateTime = \DateTime::createFromInterface(\$startDate);\n        \$endDateLimit = (clone \$startDateTime)->modify(\"+\$daysCount days\");\n        \$allAppts = \$this->apptRepo->findByDoctorAndRange(\$doctor, \$startDateTime, \$endDateLimit);");

pregReplaceInFile(__DIR__ . '/src/Service/AvailabilityService.php',
    '/\$currentDate = \(clone \$startDate\)->modify\("\+\$i days"\);/s',
    "\$currentDate = (clone \$startDateTime)->modify(\"+\$i days\");");

pregReplaceInFile(__DIR__ . '/src/Service/AvailabilityService.php',
    '/for \(\$i = 0; \$i < 45; \$i\+\+\) \{\s*\$day = \(clone \$startDate\)->modify\("\+\$i days"\);/s',
    "\$startDateTime = \DateTime::createFromInterface(\$startDate);\n        for (\$i = 0; \$i < 45; \$i++) {\n            \$day = (clone \$startDateTime)->modify(\"+\$i days\");");

pregReplaceInFile(__DIR__ . '/src/Service/AvailabilityService.php',
    '/\$current = \(clone \$date\)->setTime\(\$start->format\(\'H\'\), \$start->format\(\'i\'\)\);\s*\$endOfDay = \(clone \$date\)->setTime\(\$end->format\(\'H\'\), \$end->format\(\'i\'\)\);/s',
    "\$current = \DateTime::createFromInterface(\$date)->setTime((int)\$start->format('H'), (int)\$start->format('i'), 0);\n            \$endOfDay = \DateTime::createFromInterface(\$date)->setTime((int)\$end->format('H'), (int)\$end->format('i'), 0);");

echo "Regex Replace complete.\n";
