<?php

namespace App\Controller;

use App\Repository\RendezVousRepository;
use App\Repository\AppointmentRepository;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestEmailController extends AbstractController
{
    #[Route('/test-email', name: 'app_test_email')]
    public function testEmail(
        EmailService $emailService,
        RendezVousRepository $rendezVousRepo,
        AppointmentRepository $appointmentRepo
    ): Response
    {
        // Try to find a pending RendezVous first
        $target = $rendezVousRepo->findOneBy(['statut' => 'en_attente']);

        // Fallback to latest Appointment if no RendezVous found
        if (!$target) {
            $target = $appointmentRepo->findOneBy([], ['id' => 'DESC']);
        }

        if (!$target) {
            return new Response('No appointments or rendezvous found in database. Please book an appointment first.');
        }

        try {
            // Test confirmation email
            $emailService->sendAppointmentConfirmation($target);
            $confirmationResult = '✅ Confirmation email sent successfully!<br>';
            
            // Test reminder email
            $emailService->sendAppointmentReminder($target);
            $reminderResult = '✅ Reminder email sent successfully!<br>';
 
            // Test cancellation email
            $emailService->sendAppointmentCancellation($target);
            $cancellationResult = '✅ Cancellation email sent successfully!<br>';

            return new Response(
                '<h2>Email Test Results</h2>' .
                $confirmationResult . 
                $reminderResult . 
                $cancellationResult . 
                '<p><strong>All emails have been sent! Check your mailbox.</strong></p>' .
                '<a href="/">Back to home</a>'
            );
        } catch (\Exception $e) {
            return new Response(
                '<h2 style="color: red;">Email Test Failed</h2>' .
                '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>' .
                '<p><strong>Make sure your MAILER_DSN is correctly configured in .env</strong></p>' .
                '<a href="/">Back to home</a>'
            );
        }
    }
}