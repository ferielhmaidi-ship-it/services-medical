<?php

namespace App\Controller;

use App\Repository\RendezVousRepository;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestEmailController extends AbstractController
{
    #[Route('/test-email', name: 'app_test_email')]
    public function testEmail(
        EmailService $emailService,
        RendezVousRepository $rendezVousRepo
    ): Response
    {
        // Get first pending appointment
        $rendezVous = $rendezVousRepo->findOneBy(['statut' => 'en_attente']);

        if (!$rendezVous) {
            return new Response('No pending appointments found for testing. Please book an appointment first.');
        }

        try {
            // Test confirmation email
            $emailService->sendAppointmentConfirmation($rendezVous);
            $confirmationResult = '✅ Confirmation email sent successfully!<br>';
            
            // Test reminder email
            $emailService->sendAppointmentReminder($rendezVous);
            $reminderResult = '✅ Reminder email sent successfully!<br>';

            // Test cancellation email
            $emailService->sendAppointmentCancellation($rendezVous);
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