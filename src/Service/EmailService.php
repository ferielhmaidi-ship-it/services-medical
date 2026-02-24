<?php

namespace App\Service;

use App\Entity\Appointment;
use App\Entity\Patient;
use App\Entity\Medecin;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;

class EmailService
{
    private MailerInterface $mailer;
    private string $senderEmail;
    private string $senderName;
    private LoggerInterface $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger, string $senderEmail = 'zidayoub085@gmail.com', string $senderName = 'MediNest')
    {
        $this->mailer = $mailer;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
        $this->logger = $logger;
    }

    public function sendAppointmentConfirmation(mixed $appointment, ?Patient $patient = null, ?Medecin $doctor = null): void
    {
        $patient = $patient ?? $appointment->getPatient();
        $doctor = $doctor ?? $appointment->getDoctor();

        if (!$patient || !$doctor) {
            $this->logger->warning('Email confirmation aborted: Patient or Doctor missing from appointment.');
            return;
        }

        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->senderEmail, $this->senderName))
                ->to(new Address($patient->getEmail(), $patient->getFirstName() . ' ' . $patient->getLastName()))
                ->subject('Confirmation de rendez-vous - MediNest')
                ->htmlTemplate('emails/appointment_confirmation.html.twig')
                ->context([
                    'appointment' => $appointment,
                    'patient'     => $patient,
                    'doctor'      => $doctor,
                ]);

            $this->mailer->send($email);
            $this->logger->info(sprintf('Confirmation email sent to %s for appointment %d', $patient->getEmail(), $appointment->getId()));
        } catch (\Exception $e) {
            $this->logger->error('Failed to send confirmation email: ' . $e->getMessage());
            throw $e;
        }
    }

    public function sendAppointmentReminder(mixed $appointment, ?Patient $patient = null, ?Medecin $doctor = null): void
    {
        $patient = $patient ?? $appointment->getPatient();
        $doctor = $doctor ?? $appointment->getDoctor();

        if (!$patient || !$doctor) {
            $this->logger->warning('Email reminder aborted: Patient or Doctor missing.');
            return;
        }

        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->senderEmail, $this->senderName))
                ->to(new Address($patient->getEmail(), $patient->getFirstName() . ' ' . $patient->getLastName()))
                ->subject('Rappel : Votre rendez-vous de demain - MediNest')
                ->htmlTemplate('emails/appointment_reminder.html.twig')
                ->context([
                    'appointment' => $appointment,
                    'patient'     => $patient,
                    'doctor'      => $doctor,
                ]);

            $this->mailer->send($email);
            $this->logger->info(sprintf('Reminder email sent to %s', $patient->getEmail()));
        } catch (\Exception $e) {
            $this->logger->error('Failed to send reminder email: ' . $e->getMessage());
            throw $e;
        }
    }

    public function sendAppointmentCancellation(mixed $appointment, ?Patient $patient = null, ?Medecin $doctor = null): void
    {
        $patient = $patient ?? $appointment->getPatient();
        $doctor = $doctor ?? $appointment->getDoctor();

        if (!$patient || !$doctor) {
            $this->logger->warning('Email cancellation aborted: Patient or Doctor missing.');
            return;
        }

        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->senderEmail, $this->senderName))
                ->to(new Address($patient->getEmail(), $patient->getFirstName() . ' ' . $patient->getLastName()))
                ->subject('Annulation de rendez-vous - MediNest')
                ->htmlTemplate('emails/appointment_cancellation.html.twig')
                ->context([
                    'appointment' => $appointment,
                    'patient'     => $patient,
                    'doctor'      => $doctor,
                ]);

            $this->mailer->send($email);
            $this->logger->info(sprintf('Cancellation email sent to %s', $patient->getEmail()));
        } catch (\Exception $e) {
            $this->logger->error('Failed to send cancellation email: ' . $e->getMessage());
            throw $e;
        }
    }

    public function sendRendezVousConfirmation(mixed $rendezVous, ?Patient $patient = null, ?Medecin $doctor = null): void
    {
        $this->sendAppointmentConfirmation($rendezVous, $patient, $doctor);
    }

    public function sendRendezVousCancellation(mixed $rendezVous, ?Patient $patient = null, ?Medecin $doctor = null): void
    {
        $this->sendAppointmentCancellation($rendezVous, $patient, $doctor);
    }
}