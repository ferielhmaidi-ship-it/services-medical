<?php

function replaceInFile($path, $search, $replace)
{
    if (!file_exists($path)) {
        echo "File not found: $path\n";
        return;
    }
    $content = file_get_contents($path);
    $newContent = str_replace($search, $replace, $content);
    if ($content !== $newContent) {
        file_put_contents($path, $newContent);
        echo "Updated $path\n";
    }
    else {
        echo "No changes made to $path\n";
    }
}

// 1. AdminMedecinController.php
replaceInFile(
    __DIR__ . '/src/Controller/AdminMedecinController.php',
    "    private UserPasswordHasherInterface \$passwordHasher;\n    private DoctorVerificationService \$doctorVerificationService;\n\n    public function __construct(UserPasswordHasherInterface \$passwordHasher, DoctorVerificationService \$doctorVerificationService)\n    {\n        \$this->passwordHasher = \$passwordHasher;\n        \$this->doctorVerificationService = \$doctorVerificationService;\n    }",
    "    private UserPasswordHasherInterface \$passwordHasher;\n\n    public function __construct(UserPasswordHasherInterface \$passwordHasher)\n    {\n        \$this->passwordHasher = \$passwordHasher;\n    }"
);
replaceInFile(
    __DIR__ . '/src/Controller/AdminMedecinController.php',
    "    private UserPasswordHasherInterface \$passwordHasher;\r\n    private DoctorVerificationService \$doctorVerificationService;\r\n\r\n    public function __construct(UserPasswordHasherInterface \$passwordHasher, DoctorVerificationService \$doctorVerificationService)\r\n    {\r\n        \$this->passwordHasher = \$passwordHasher;\r\n        \$this->doctorVerificationService = \$doctorVerificationService;\r\n    }",
    "    private UserPasswordHasherInterface \$passwordHasher;\r\n\r\n    public function __construct(UserPasswordHasherInterface \$passwordHasher)\r\n    {\r\n        \$this->passwordHasher = \$passwordHasher;\r\n    }"
);

// 2. BookingController.php
replaceInFile(
    __DIR__ . '/src/Controller/BookingController.php',
    "        \$isPatient = (\$user instanceof Patient);\n\n        if (\$isPatient && \$user instanceof Patient) {",
    "        if (\$user instanceof Patient) {"
);
replaceInFile(
    __DIR__ . '/src/Controller/BookingController.php',
    "        \$isPatient = (\$user instanceof Patient);\r\n\r\n        if (\$isPatient && \$user instanceof Patient) {",
    "        if (\$user instanceof Patient) {"
);

// 3. CalendarController.php
replaceInFile(__DIR__ . '/src/Controller/CalendarController.php', "private PatientRepository \$patientRepo;\n\n    public function __construct(PatientRepository \$patientRepo)\n    {\n        \$this->patientRepo = \$patientRepo;\n    }", "");
replaceInFile(__DIR__ . '/src/Controller/CalendarController.php', "private PatientRepository \$patientRepo;\r\n\r\n    public function __construct(PatientRepository \$patientRepo)\r\n    {\r\n        \$this->patientRepo = \$patientRepo;\r\n    }", "");
replaceInFile(__DIR__ . '/src/Controller/CalendarController.php', "\$doctorId = \$this->getUser()->getId();\n        \$doctor = \$this->getUser();", "/** @var \App\Entity\Medecin \$user */\n        \$user = \$this->getUser();\n        \$doctorId = \$user->getId();\n        \$doctor = \$user;");
replaceInFile(__DIR__ . '/src/Controller/CalendarController.php', "\$doctorId = \$this->getUser()->getId();\r\n        \$doctor = \$this->getUser();", "/** @var \App\Entity\Medecin \$user */\r\n        \$user = \$this->getUser();\r\n        \$doctorId = \$user->getId();\r\n        \$doctor = \$user;");
replaceInFile(__DIR__ . '/src/Controller/CalendarController.php', "\$doctorId = \$this->getUser()->getId();", "/** @var \App\Entity\Medecin \$user */\n        \$user = \$this->getUser();\n        \$doctorId = \$user->getId();");
replaceInFile(__DIR__ . '/src/Controller/CalendarController.php', "/** @var \App\Entity\Medecin \$user */\n        \$user = \$this->getUser();\n        /** @var \App\Entity\Medecin \$user */\n        \$user = \$this->getUser();", "/** @var \App\Entity\Medecin \$user */\n        \$user = \$this->getUser();");
replaceInFile(__DIR__ . '/src/Controller/CalendarController.php', "\$indisp->setDoctorId(\$this->getUser()->getId());", "/** @var \App\Entity\Medecin \$user */\n        \$user = \$this->getUser();\n        \$indisp->setDoctorId(\$user->getId());");

replaceInFile(__DIR__ . '/src/Controller/CalendarController.php', "\$candidateRendezVous = \$rendezVousRepository->createQueryBuilder('rv')\n            ->where('rv.patient = :patient')\n            ->andWhere('rv.doctor = :doctor')\n            ->andWhere('rv.appointmentDate BETWEEN :dayStart AND :dayEnd')", "\$candidateRendezVous = \$appointmentRepository->createQueryBuilder('rv')\n            ->where('rv.patient = :patient')\n            ->andWhere('rv.doctor = :doctor')\n            ->andWhere('rv.date BETWEEN :dayStart AND :dayEnd')");
replaceInFile(__DIR__ . '/src/Controller/CalendarController.php', "\$candidateRendezVous = \$rendezVousRepository->createQueryBuilder('rv')\r\n            ->where('rv.patient = :patient')\r\n            ->andWhere('rv.doctor = :doctor')\r\n            ->andWhere('rv.appointmentDate BETWEEN :dayStart AND :dayEnd')", "\$candidateRendezVous = \$appointmentRepository->createQueryBuilder('rv')\r\n            ->where('rv.patient = :patient')\r\n            ->andWhere('rv.doctor = :doctor')\r\n            ->andWhere('rv.date BETWEEN :dayStart AND :dayEnd')");
replaceInFile(__DIR__ . '/src/Controller/CalendarController.php', "\$delta = abs(\$rendezVous->getAppointmentDate()->getTimestamp() - \$targetDateTime->getTimestamp());", "\$rvTargetTime = (new \DateTimeImmutable(\$rendezVous->getDate()->format('Y-m-d H:i:s')))->setTime((int) \$rendezVous->getStartTime()->format('H'), (int) \$rendezVous->getStartTime()->format('i'));\n            \$delta = abs(\$rvTargetTime->getTimestamp() - \$targetDateTime->getTimestamp());");

// 4. CreateAdminCommand.php
replaceInFile(__DIR__ . '/src/Command/CreateAdminCommand.php', "        foreach (\$questions as \$name => \$question) {\n            \$answer = \$this->getHelper('question')->ask(\$input, \$output, \$question);", "        /** @var \\Symfony\\Component\\Console\\Helper\\QuestionHelper \$helper */\n        \$helper = \$this->getHelper('question');\n        foreach (\$questions as \$name => \$question) {\n            \$answer = \$helper->ask(\$input, \$output, \$question);");
replaceInFile(__DIR__ . '/src/Command/CreateAdminCommand.php', "        foreach (\$questions as \$name => \$question) {\r\n            \$answer = \$this->getHelper('question')->ask(\$input, \$output, \$question);", "        /** @var \\Symfony\\Component\\Console\\Helper\\QuestionHelper \$helper */\r\n        \$helper = \$this->getHelper('question');\r\n        foreach (\$questions as \$name => \$question) {\r\n            \$answer = \$helper->ask(\$input, \$output, \$question);");

// 5. DocumentPdfController.php
replaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', "getIdrapport()", "getId()");
replaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', "getIdpatient()", "getPatient()");
replaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', "getIdmedecin()", "getMedecin()");
replaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', "getIdordonnance()", "getId()");
replaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', "getIddocument()", "getId()");
replaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', "getDateordonnance()", "getDateOrdonnance()");
replaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', "getPrenom()", "getFirstName()");
replaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', "getNom()", "getLastName()");
replaceInFile(__DIR__ . '/src/Controller/DocumentPdfController.php', "strlen(\$pdfContent)", "(string) strlen(\$pdfContent)");

// 6. IaController.php
replaceInFile(__DIR__ . '/src/Controller/IaController.php', "    public function __construct(\n        private string \$uploadDirectory\n    ) {}", "    public function __construct() {}");
replaceInFile(__DIR__ . '/src/Controller/IaController.php', "    public function __construct(\r\n        private string \$uploadDirectory\r\n    ) {}", "    public function __construct() {}");
replaceInFile(__DIR__ . '/src/Controller/IaController.php', "\$date = \$r->getAppointmentDate()?->format('Y-m-d H:i');\n            \$dbText .= \"- RDV #{\$r->getId()} | Date: {\$date}\\n\";\n            \$dbText .= \"  Patient: {\$r->getPatient()->getFullName()}\\n\";\n            \$dbText .= \"  Médecin: Dr {\$r->getDoctor()->getFullName()}\\n\";\n            \$dbText .= \"  Statut: {\$r->getStatut()}\\n\\n\";", "\$dateStr = \$r->getDate()?->format('Y-m-d') . ' ' . \$r->getStartTime()?->format('H:i');\n            \$dbText .= \"- RDV #{\$r->getId()} | Date: {\$dateStr}\\n\";\n            \$dbText .= \"  Patient: {\$r->getPatient()->getFullName()}\\n\";\n            \$dbText .= \"  Médecin: Dr {\$r->getDoctor()->getFullName()}\\n\";\n            \$dbText .= \"  Statut: {\$r->getStatus()}\\n\\n\";");
replaceInFile(__DIR__ . '/src/Controller/IaController.php', "\$date = \$r->getAppointmentDate()?->format('Y-m-d H:i');\r\n            \$dbText .= \"- RDV #{\$r->getId()} | Date: {\$date}\\n\";\r\n            \$dbText .= \"  Patient: {\$r->getPatient()->getFullName()}\\n\";\r\n            \$dbText .= \"  Médecin: Dr {\$r->getDoctor()->getFullName()}\\n\";\r\n            \$dbText .= \"  Statut: {\$r->getStatut()}\\n\\n\";", "\$dateStr = \$r->getDate()?->format('Y-m-d') . ' ' . \$r->getStartTime()?->format('H:i');\n            \$dbText .= \"- RDV #{\$r->getId()} | Date: {\$dateStr}\\n\";\n            \$dbText .= \"  Patient: {\$r->getPatient()->getFullName()}\\n\";\n            \$dbText .= \"  Médecin: Dr {\$r->getDoctor()->getFullName()}\\n\";\n            \$dbText .= \"  Statut: {\$r->getStatus()}\\n\\n\";");

// 7. PatientDashboardController.php
replaceInFile(__DIR__ . '/src/Controller/PatientDashboardController.php', "array_values(array_filter(", "array_filter(");
replaceInFile(__DIR__ . '/src/Controller/PatientDashboardController.php', "ordonnances']\n            ));", "ordonnances']\n            );");
replaceInFile(__DIR__ . '/src/Controller/PatientDashboardController.php', "ordonnances']\r\n            ));", "ordonnances']\r\n            );");

// 8. QuestionController.php
replaceInFile(__DIR__ . '/src/Controller/QuestionController.php', "if (\$question->isLikedBy(\$user)) {", "/** @var \App\Entity\BaseUser \$user */\n        if (\$question->isLikedBy(\$user)) {");

// 9. ReponseController.php
replaceInFile(__DIR__ . '/src/Controller/ReponseController.php', "if (\$reponse->isLikedBy(\$user)) {", "/** @var \App\Entity\BaseUser \$user */\n        if (\$reponse->isLikedBy(\$user)) {");

// 10. TestEmailController.php
replaceInFile(__DIR__ . '/src/Controller/TestEmailController.php', "            \$emailService->sendAppointmentConfirmation(\$appointment);\n            \$confirmationResult = '✅ Confirmation email sent successfully!<br>';\n            \n            \$emailService->sendAppointmentReminder(\$appointment);\n            \$reminderResult = '✅ Reminder email sent successfully!<br>';\n\n            \$emailService->sendAppointmentCancellation(\$appointment);\n            \$cancellationResult = '✅ Cancellation email sent successfully!<br>';", "            \$patient = \$appointment->getPatient();\n            \$doctor = \$appointment->getDoctor();\n\n            \$emailService->sendAppointmentConfirmation(\$appointment, \$patient, \$doctor);\n            \$confirmationResult = '✅ Confirmation email sent successfully!<br>';\n            \n            \$emailService->sendAppointmentReminder(\$appointment, \$patient, \$doctor);\n            \$reminderResult = '✅ Reminder email sent successfully!<br>';\n\n            \$emailService->sendAppointmentCancellation(\$appointment, \$patient, \$doctor);\n            \$cancellationResult = '✅ Cancellation email sent successfully!<br>';");
replaceInFile(__DIR__ . '/src/Controller/TestEmailController.php', "            \$emailService->sendAppointmentConfirmation(\$appointment);\r\n            \$confirmationResult = '✅ Confirmation email sent successfully!<br>';\r\n            \r\n            \$emailService->sendAppointmentReminder(\$appointment);\r\n            \$reminderResult = '✅ Reminder email sent successfully!<br>';\r\n\r\n            \$emailService->sendAppointmentCancellation(\$appointment);\r\n            \$cancellationResult = '✅ Cancellation email sent successfully!<br>';", "            \$patient = \$appointment->getPatient();\r\n            \$doctor = \$appointment->getDoctor();\r\n\r\n            \$emailService->sendAppointmentConfirmation(\$appointment, \$patient, \$doctor);\r\n            \$confirmationResult = '✅ Confirmation email sent successfully!<br>';\r\n            \r\n            \$emailService->sendAppointmentReminder(\$appointment, \$patient, \$doctor);\r\n            \$reminderResult = '✅ Reminder email sent successfully!<br>';\r\n\r\n            \$emailService->sendAppointmentCancellation(\$appointment, \$patient, \$doctor);\r\n            \$cancellationResult = '✅ Cancellation email sent successfully!<br>';");

// 11. Question.php
replaceInFile(__DIR__ . '/src/Entity/Question.php', "public function getLikedBy(): Collection\n    {\n        return \$this->likedBy;\n    }", "public function getLikedBy(): Collection\n    {\n        return new ArrayCollection(array_merge(\n            \$this->likedByPatients->toArray(),\n            \$this->likedByMedecins->toArray()\n        ));\n    }");
replaceInFile(__DIR__ . '/src/Entity/Question.php', "public function getLikedBy(): Collection\r\n    {\r\n        return \$this->likedBy;\r\n    }", "public function getLikedBy(): Collection\r\n    {\r\n        return new ArrayCollection(array_merge(\r\n            \$this->likedByPatients->toArray(),\r\n            \$this->likedByMedecins->toArray()\r\n        ));\r\n    }");

// 12. Reponse.php
replaceInFile(__DIR__ . '/src/Entity/Reponse.php', "public function getLikedBy(): Collection\n    {\n        return \$this->likedBy;\n    }", "public function getLikedBy(): Collection\n    {\n        return new ArrayCollection(array_merge(\n            \$this->likedByPatients->toArray(),\n            \$this->likedByMedecins->toArray()\n        ));\n    }");
replaceInFile(__DIR__ . '/src/Entity/Reponse.php', "public function getLikedBy(): Collection\r\n    {\r\n        return \$this->likedBy;\r\n    }", "public function getLikedBy(): Collection\r\n    {\r\n        return new ArrayCollection(array_merge(\r\n            \$this->likedByPatients->toArray(),\r\n            \$this->likedByMedecins->toArray()\r\n        ));\r\n    }");

// 13. AvailabilityService.php
replaceInFile(__DIR__ . '/src/Service/AvailabilityService.php', "\$endDate = (clone \$startDate)->modify('+7 days');\n        \$currentDate = clone \$startDate;", "\$currentDate = \\DateTime::createFromInterface(\$startDate);\n        \$endDate = (clone \$currentDate)->modify('+7 days');");
replaceInFile(__DIR__ . '/src/Service/AvailabilityService.php', "\$endDate = (clone \$startDate)->modify('+7 days');\r\n        \$currentDate = clone \$startDate;", "\$currentDate = \\DateTime::createFromInterface(\$startDate);\r\n        \$endDate = (clone \$currentDate)->modify('+7 days');");
replaceInFile(__DIR__ . '/src/Service/AvailabilityService.php', "\$endDateLimit = (clone \$startDate)->modify(\"+\$daysCount days\");", "\$startDateTime = \\DateTime::createFromInterface(\$startDate);\n        \$endDateLimit = (clone \$startDateTime)->modify(\"+\$daysCount days\");");
replaceInFile(__DIR__ . '/src/Service/AvailabilityService.php', "\$allAppts = \$this->apptRepo->findByDoctorAndRange(\$doctor, \$startDate, \$endDateLimit);", "\$allAppts = \$this->apptRepo->findByDoctorAndRange(\$doctor, \$startDateTime, \$endDateLimit);");
replaceInFile(__DIR__ . '/src/Service/AvailabilityService.php', "\$currentDate = (clone \$startDate)->modify(\"+\$i days\");", "\$currentDate = (clone \$startDateTime)->modify(\"+\$i days\");");
replaceInFile(__DIR__ . '/src/Service/AvailabilityService.php', "for (\$i = 0; \$i < 45; \$i++) {\n            \$day = (clone \$startDate)->modify(\"+\$i days\");", "\$startDateTime = \\DateTime::createFromInterface(\$startDate);\n        for (\$i = 0; \$i < 45; \$i++) {\n            \$day = (clone \$startDateTime)->modify(\"+\$i days\");");
replaceInFile(__DIR__ . '/src/Service/AvailabilityService.php', "for (\$i = 0; \$i < 45; \$i++) {\r\n            \$day = (clone \$startDate)->modify(\"+\$i days\");", "\$startDateTime = \\DateTime::createFromInterface(\$startDate);\r\n        for (\$i = 0; \$i < 45; \$i++) {\r\n            \$day = (clone \$startDateTime)->modify(\"+\$i days\");");
replaceInFile(__DIR__ . '/src/Service/AvailabilityService.php', "\$current = (clone \$date)->setTime(\$start->format('H'), \$start->format('i'));\n            \$endOfDay = (clone \$date)->setTime(\$end->format('H'), \$end->format('i'));", "\$current = \\DateTime::createFromInterface(\$date)->setTime((int)\$start->format('H'), (int)\$start->format('i'), 0);\n            \$endOfDay = \\DateTime::createFromInterface(\$date)->setTime((int)\$end->format('H'), (int)\$end->format('i'), 0);");
replaceInFile(__DIR__ . '/src/Service/AvailabilityService.php', "\$current = (clone \$date)->setTime(\$start->format('H'), \$start->format('i'));\r\n            \$endOfDay = (clone \$date)->setTime(\$end->format('H'), \$end->format('i'));", "\$current = \\DateTime::createFromInterface(\$date)->setTime((int)\$start->format('H'), (int)\$start->format('i'), 0);\r\n            \$endOfDay = \\DateTime::createFromInterface(\$date)->setTime((int)\$end->format('H'), (int)\$end->format('i'), 0);");

echo "All complete.\n";
