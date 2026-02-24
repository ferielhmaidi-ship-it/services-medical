<?php

namespace App\Command;

use App\Repository\AppointmentRepository;
use App\Repository\PatientRepository;
use App\Repository\MedecinRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: "app:appointment:send-reminders",
    description: "Send email reminders for appointments scheduled for tomorrow"
)]
class SendAppointmentRemindersCommand extends Command
{
    private AppointmentRepository $appointmentRepository;
    private PatientRepository $patientRepository;
    private MedecinRepository $medecinRepository;
    private EmailService $emailService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        AppointmentRepository $appointmentRepository,
        PatientRepository $patientRepository,
        MedecinRepository $medecinRepository,
        EmailService $emailService,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->appointmentRepository = $appointmentRepository;
        $this->patientRepository = $patientRepository;
        $this->medecinRepository = $medecinRepository;
        $this->emailService = $emailService;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tomorrow = (new \DateTime("+1 day"))->format("Y-m-d");

        $io->title("Sending Appointment Reminders for " . $tomorrow);

        $appointments = $this->appointmentRepository->createQueryBuilder("a")
            ->where("a.date = :tomorrow")
            ->andWhere("a.reminderSent = :false")
            ->andWhere("a.status = :status")
            ->setParameter("tomorrow", $tomorrow)
            ->setParameter("false", false)
            ->setParameter("status", "scheduled")
            ->getQuery()
            ->getResult();

        if (empty($appointments)) {
            $io->info("No appointments scheduled for tomorrow that need a reminder.");
            return Command::SUCCESS;
        }

        $io->progressStart(count($appointments));
        $count = 0;

        foreach ($appointments as $appointment) {
            $patient = $this->patientRepository->find($appointment->getPatientId());
            $doctor = $this->medecinRepository->find($appointment->getDoctorId());

            if ($patient && $doctor) {
                try {
                    $this->emailService->sendAppointmentReminder($appointment, $patient, $doctor);
                    $appointment->setReminderSent(true);
                    $count++;
                } catch (\Exception $e) {
                    $io->error(sprintf("Failed to send reminder for appointment ID %d: %s", $appointment->getId(), $e->getMessage()));
                }
            } else {
                $io->warning(sprintf("Skipping appointment ID %d: Patient or Doctor not found.", $appointment->getId()));
            }

            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->success(sprintf("Successfully sent %d reminders.", $count));

        return Command::SUCCESS;
    }
}