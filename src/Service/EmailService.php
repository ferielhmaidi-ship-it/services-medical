<?php

namespace App\Service;

use App\Entity\RendezVous;
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

    public function sendAppointmentConfirmation(RendezVous $rendezVous): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@medinest.com', 'MediNest'))
            ->to(new Address($rendezVous->getPatient()->getEmail(), $rendezVous->getPatient()->getFirstName() . ' ' . $rendezVous->getPatient()->getLastName()))
            ->subject('Confirmation de rendez-vous - MediNest')
            ->htmlTemplate('emails/appointment_confirmation.html.twig')
            ->context([
                'rendezVous' => $rendezVous,
                'patient' => $rendezVous->getPatient(),
                'doctor' => $rendezVous->getDoctor(),
            ]);

        $this->mailer->send($email);
    }

    public function sendAppointmentReminder(RendezVous $rendezVous): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@medinest.com', 'MediNest'))
            ->to(new Address($rendezVous->getPatient()->getEmail(), $rendezVous->getPatient()->getFirstName() . ' ' . $rendezVous->getPatient()->getLastName()))
            ->subject('Rappel : Rendez-vous aujourd\'hui - MediNest')
            ->htmlTemplate('emails/appointment_reminder.html.twig')
            ->context([
                'rendezVous' => $rendezVous,
                'patient' => $rendezVous->getPatient(),
                'doctor' => $rendezVous->getDoctor(),
            ]);

        $this->mailer->send($email);
    }

    public function sendAppointmentCancellation(RendezVous $rendezVous): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@medinest.com', 'MediNest'))
            ->to(new Address($rendezVous->getPatient()->getEmail(), $rendezVous->getPatient()->getFirstName() . ' ' . $rendezVous->getPatient()->getLastName()))
            ->subject('Annulation de rendez-vous - MediNest')
            ->htmlTemplate('emails/appointment_cancellation.html.twig')
            ->context([
                'rendezVous' => $rendezVous,
                'patient' => $rendezVous->getPatient(),
                'doctor' => $rendezVous->getDoctor(),
            ]);

        $this->mailer->send($email);
    }
}