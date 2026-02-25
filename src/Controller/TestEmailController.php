<?php

namespace App\Controller;

use App\Repository\AppointmentRepository;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestEmailController extends AbstractController
{
    #[Route('/test-email', name: 'test_email')]
    public function testEmail(
        EmailService $emailService,
        AppointmentRepository $appointmentRepo
    ): Response
    {
        $appointment = $appointmentRepo->findOneBy(['status' => 'pending']);

        if (!$appointment) {
            return new Response('No pending appointments found for testing. Please book an appointment first.');
        }

        try {
            $emailService->sendAppointmentConfirmation($appointment);
            $confirmationResult = '✅ Confirmation email sent successfully!<br>';
            
            $emailService->sendAppointmentReminder($appointment);
            $reminderResult = '✅ Reminder email sent successfully!<br>';

            $emailService->sendAppointmentCancellation($appointment);
            $cancellationResult = '✅ Cancellation email sent successfully!<br>';

            return new Response(
                '<h2>Email Test Results:</h2>' .
                $confirmationResult .
                $reminderResult .
                $cancellationResult .
                '<br>All emails sent successfully!'
            );
        } catch (\Exception $e) {
            return new Response(
                '<h2>Email Test Failed:</h2>' .
                '<p style="color: red;">Error: ' . $e->getMessage() . '</p>' .
                '<pre>' . $e->getTraceAsString() . '</pre>'
            );
        }
    }
}