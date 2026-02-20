<?php

namespace App\Service;

use App\Entity\Appointment;
use App\Entity\Patient;
use App\Entity\Medecin;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendAppointmentConfirmation(Appointment $appointment, Patient $patient, Medecin $doctor): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@medinest.com', 'MediNest'))
            ->to(new Address($patient->getEmail(), $patient->getFirstName() . ' ' . $patient->getLastName()))
            ->subject('Confirmation de rendez-vous - MediNest')
            ->htmlTemplate('emails/appointment_confirmation.html.twig')
            ->context([
            'appointment' => $appointment,
            'patient' => $patient,
            'doctor' => $doctor,
        ]);

        $this->mailer->send($email);
    }

    public function sendAppointmentReminder(Appointment $appointment, Patient $patient, Medecin $doctor): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@medinest.com', 'MediNest'))
            ->to(new Address($patient->getEmail(), $patient->getFirstName() . ' ' . $patient->getLastName()))
            ->subject('Rappel : Votre rendez-vous de demain - MediNest')
            ->htmlTemplate('emails/appointment_reminder.html.twig')
            ->context([
            'appointment' => $appointment,
            'patient' => $patient,
            'doctor' => $doctor,
        ]);

        $this->mailer->send($email);
    }

    public function sendAppointmentCancellation(Appointment $appointment, Patient $patient, Medecin $doctor): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@medinest.com', 'MediNest'))
            ->to(new Address($patient->getEmail(), $patient->getFirstName() . ' ' . $patient->getLastName()))
            ->subject('Annulation de rendez-vous - MediNest')
            ->htmlTemplate('emails/appointment_cancellation.html.twig')
            ->context([
            'appointment' => $appointment,
            'patient' => $patient,
            'doctor' => $doctor,
        ]);

        $this->mailer->send($email);
    }

    public function sendRendezVousConfirmation(mixed $rendezVous, Patient $patient, Medecin $doctor): void
    {
        // $rendezVous can be Appointment or RendezVous, templates use "appointment" variable
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@medinest.com', 'MediNest'))
            ->to(new Address($patient->getEmail(), $patient->getFirstName() . ' ' . $patient->getLastName()))
            ->subject('Confirmation de rendez-vous - MediNest')
            ->htmlTemplate('emails/appointment_confirmation.html.twig')
            ->context([
            'appointment' => $rendezVous,
            'patient' => $patient,
            'doctor' => $doctor,
        ]);

        $this->mailer->send($email);
    }

    public function sendRendezVousCancellation(mixed $rendezVous, Patient $patient, Medecin $doctor): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@medinest.com', 'MediNest'))
            ->to(new Address($patient->getEmail(), $patient->getFirstName() . ' ' . $patient->getLastName()))
            ->subject('Annulation de rendez-vous - MediNest')
            ->htmlTemplate('emails/appointment_cancellation.html.twig')
            ->context([
            'appointment' => $rendezVous,
            'patient' => $patient,
            'doctor' => $doctor,
        ]);

        $this->mailer->send($email);
    }
}