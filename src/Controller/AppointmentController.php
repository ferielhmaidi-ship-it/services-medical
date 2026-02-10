<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\Patient;
use App\Entity\Medecin;
use App\Entity\Feedback;
use App\Form\RendezVousType;
use App\Form\FeedbackType;
use App\Service\EmailService;
use App\Service\SentimentAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppointmentController extends AbstractController
{
    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    #[Route('/appointment', name: 'app_appointment')]
    public function index(
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        $rendezVous = new RendezVous();
        $form = $this->createForm(RendezVousType::class, $rendezVous);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Ensure default message when none provided
                if (empty($rendezVous->getMessage())) {
                    $rendezVous->setMessage('Consultation de routine');
                }

                // Set status and createdAt
                $rendezVous->setStatut('en_attente');
                $rendezVous->setCreatedAt(new \DateTimeImmutable());

                // Persist appointment
                $em->persist($rendezVous);
                $em->flush();

                // ✅ SEND CONFIRMATION EMAIL
                try {
                    $this->emailService->sendAppointmentConfirmation($rendezVous);
                    $emailSent = true;
                } catch (\Exception $e) {
                    $emailSent = false;
                    error_log('Email sending failed: ' . $e->getMessage());
                }

                // Store patient ID in session and redirect to success
                $patient = $rendezVous->getPatient();
                if ($patient) {
                    $request->getSession()->set('patient_id', $patient->getId());
                    
                    if ($emailSent) {
                        $this->addFlash('success', 'Appointment created successfully! A confirmation email has been sent.');
                    } else {
                        $this->addFlash('success', 'Appointment created successfully!');
                        $this->addFlash('warning', 'However, the confirmation email could not be sent.');
                    }
                    
                    return $this->redirectToRoute('app_appointment_success', ['patientId' => $patient->getId()]);
                }

                $this->addFlash('success', 'Appointment created.');
                return $this->redirectToRoute('app_appointment');
            } catch (\Throwable $e) {
                $this->addFlash('error', 'Error: ' . $e->getMessage());
                error_log('Appointment Error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            }
        } elseif ($form->isSubmitted()) {
            // Rely on entity constraints; show a concise message
            $this->addFlash('error', 'Please correct the highlighted errors in the form.');
        }

        return $this->render('rendezvous/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/my-appointments', name: 'app_my_appointments')]
    public function myAppointments(
        EntityManagerInterface $em,
        Request $request
    ): Response
    {
        $patientId = $request->getSession()->get('patient_id');
        
        // If no patient in session, redirect to book appointment
        if (!$patientId) {
            $this->addFlash('warning', 'Please book an appointment first to view your appointments.');
            return $this->redirectToRoute('app_appointment');
        }
        
        $patient = $em->getRepository(Patient::class)->find($patientId);

        if (!$patient) {
            $request->getSession()->remove('patient_id');
            $this->addFlash('error', 'Patient not found. Please book an appointment.');
            return $this->redirectToRoute('app_appointment');
        }

        $search = $request->query->get('search', '');
        $statut = $request->query->get('statut', '');

        // Get all appointments for this patient and filter them
        $allAppointments = $patient->getRendezVous();
        
        $rendezVous = $allAppointments->filter(function($rdv) use ($search, $statut) {
            $matchSearch = empty($search) || 
                stripos($rdv->getDoctor()->getFirstName(), $search) !== false ||
                stripos($rdv->getDoctor()->getLastName(), $search) !== false;

            $matchStatut = empty($statut) || $rdv->getStatut() === $statut;

            return $matchSearch && $matchStatut;
        });

        return $this->render('rendezvous/index.html.twig', [
            'rendezVous' => $rendezVous,
            'patient' => $patient,
            'search' => $search,
            'statut' => $statut,
        ]);
    }

    #[Route('/appointment/success/{patientId}', name: 'app_appointment_success')]
    public function success(Patient $patientId, EntityManagerInterface $em): Response
    {
        $patient = $patientId;
        $appointments = $patient->getRendezVous();
        
        return $this->render('rendezvous/success.html.twig', [
            'patient' => $patient,
            'appointments' => $appointments,
        ]);
    }

    #[Route('/appointment/{id}/edit', name: 'app_appointment_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        RendezVous $rendezVous,
        EntityManagerInterface $em
    ): Response
    {
        // Check if appointment is still pending
        if ($rendezVous->getStatut() !== 'en_attente') {
            $this->addFlash('error', 'Only pending appointments can be edited.');
            return $this->redirectToRoute('app_my_appointments');
        }

        // Verify patient owns this appointment
        $patientId = $request->getSession()->get('patient_id');
        if (!$patientId || $rendezVous->getPatient()->getId() !== $patientId) {
            $this->addFlash('error', 'You do not have permission to edit this appointment.');
            return $this->redirectToRoute('app_my_appointments');
        }

        $form = $this->createForm(RendezVousType::class, $rendezVous);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                $this->addFlash('success', 'Appointment updated successfully!');
                return $this->redirectToRoute('app_my_appointments');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error updating appointment: ' . $e->getMessage());
            }
        }

        return $this->render('rendezvous/edit.html.twig', [
            'form' => $form->createView(),
            'rendezVous' => $rendezVous,
        ]);
    }

    #[Route('/appointment/{id}/cancel', name: 'app_appointment_cancel', methods: ['POST'])]
    public function cancel(
        Request $request,
        RendezVous $rendezVous,
        EntityManagerInterface $em
    ): Response
    {
        // Verify patient owns this appointment
        $patientId = $request->getSession()->get('patient_id');
        if (!$patientId || $rendezVous->getPatient()->getId() !== $patientId) {
            $this->addFlash('error', 'You do not have permission to cancel this appointment.');
            return $this->redirectToRoute('app_my_appointments');
        }

        // Verify CSRF token
        if (!$this->isCsrfTokenValid('cancel' . $rendezVous->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Invalid security token. Please try again.');
            return $this->redirectToRoute('app_my_appointments');
        }

        // Only allow cancellation of pending appointments
        if ($rendezVous->getStatut() !== 'en_attente') {
            $this->addFlash('error', 'Only pending appointments can be cancelled.');
            return $this->redirectToRoute('app_my_appointments');
        }

        try {
            $rendezVous->setStatut('annule');
            $em->flush();

            // ✅ SEND CANCELLATION EMAIL
            try {
                $this->emailService->sendAppointmentCancellation($rendezVous);
                $this->addFlash('success', 'Appointment cancelled successfully. A confirmation email has been sent.');
            } catch (\Exception $e) {
                $this->addFlash('success', 'Appointment cancelled successfully.');
                $this->addFlash('warning', 'However, the cancellation email could not be sent.');
                error_log('Email sending failed: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error cancelling appointment: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_my_appointments');
    }

    #[Route('/doctor/appointments', name: 'app_doctor_appointments', methods: ['GET'])]
    public function doctorAppointments(
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        $medecins = $em->getRepository(Medecin::class)->findAll();
        $selectedMedecin = null;
        $appointments = [];

        $doctorid = $request->query->get('doctor_id');
        $search = $request->query->get('search', '');
        $statut = $request->query->get('statut', '');

        if ($doctorid) {
            $selectedMedecin = $em->getRepository(Medecin::class)->find($doctorid);
            if ($selectedMedecin) {
                // Use repository search with optional search term and statut filter
                $appointments = $em->getRepository(RendezVous::class)->searchByDoctor($selectedMedecin, $search ?: null, $statut ?: null);
            }
        }

        return $this->render('rendezvous/doctor_appointments.html.twig', [
            'medecins' => $medecins,
            'selectedMedecin' => $selectedMedecin,
            'appointments' => $appointments,
            'search' => $search,
            'statut' => $statut,
        ]);
    }

    #[Route('/appointment/{id}/doctor/complete', name: 'app_appointment_doctor_complete', methods: ['POST'])]
    public function doctorMarkComplete(
        Request $request,
        RendezVous $rendezVous,
        EntityManagerInterface $em
    ): Response
    {
        // Verify CSRF token
        if (!$this->isCsrfTokenValid('complete' . $rendezVous->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid security token.');
            return $this->redirectToRoute('app_doctor_appointments', ['doctor_id' => $rendezVous->getDoctor()->getId()]);
        }

        try {
            $rendezVous->setStatut('termine');
            $em->flush();
            $this->addFlash('success', 'Appointment marked as completed.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error updating appointment: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_doctor_appointments', ['doctor_id' => $rendezVous->getDoctor()->getId()]);
    }

    #[Route('/appointment/{id}/doctor/cancel', name: 'app_appointment_doctor_cancel', methods: ['POST'])]
    public function doctorMarkCancelled(
        Request $request,
        RendezVous $rendezVous,
        EntityManagerInterface $em
    ): Response
    {
        // Verify CSRF token
        if (!$this->isCsrfTokenValid('cancel' . $rendezVous->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid security token.');
            return $this->redirectToRoute('app_doctor_appointments', ['doctor_id' => $rendezVous->getDoctor()->getId()]);
        }

        try {
            $rendezVous->setStatut('annule');
            $em->flush();

            // ✅ SEND CANCELLATION EMAIL (when doctor cancels)
            try {
                $this->emailService->sendAppointmentCancellation($rendezVous);
                $this->addFlash('success', 'Appointment marked as cancelled. Patient has been notified by email.');
            } catch (\Exception $e) {
                $this->addFlash('success', 'Appointment marked as cancelled.');
                error_log('Email sending failed: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error updating appointment: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_doctor_appointments', ['doctor_id' => $rendezVous->getDoctor()->getId()]);
    }

    #[Route('/appointment/{id}/feedback', name: 'app_appointment_feedback', methods: ['GET', 'POST'])]
    public function addFeedback(
        RendezVous $rendezVous,
        Request $request,
        EntityManagerInterface $em,
        SentimentAnalysisService $sentimentService,
        LoggerInterface $logger
    ): Response {
        // Check if patient owns this appointment and it's completed
        $patientId = $request->getSession()->get('patient_id');
        if (!$patientId || $rendezVous->getPatient()->getId() !== $patientId) {
            $this->addFlash('error', 'You can only add feedback to your own appointments.');
            return $this->redirectToRoute('app_my_appointments');
        }

        if ($rendezVous->getStatut() !== 'termine') {
            $this->addFlash('error', 'You can only add feedback to completed appointments.');
            return $this->redirectToRoute('app_my_appointments');
        }

        // Check if feedback already exists
        $existingFeedback = $em->getRepository(Feedback::class)->findOneBy(['rendezVous' => $rendezVous]);
        if ($existingFeedback) {
            $this->addFlash('warning', 'You have already provided feedback for this appointment.');
            return $this->redirectToRoute('app_my_appointments');
        }

        $feedback = new Feedback();
        $feedback->setPatient($rendezVous->getPatient());
        $feedback->setMedecin($rendezVous->getDoctor());
        $feedback->setRendezVous($rendezVous);

        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // 🔍 Analyze sentiment using Flask API before persisting
                $analysisResult = $sentimentService->analyzeSentiment(
                    $feedback->getComment(),
                    $feedback->getRating()
                );

                if ($analysisResult) {
                    $feedback->setSentimentScore((float)$analysisResult['final_score']);
                    $logger->info('✅ Sentiment score calculated in appointment feedback', [
                        'score' => $analysisResult['final_score'],
                        'label' => $analysisResult['sentiment_label'],
                        'feedback_id' => $feedback->getId()
                    ]);
                } else {
                    // Fallback: use rating if sentiment analysis fails
                    $feedback->setSentimentScore((float)$feedback->getRating());
                    $logger->warning('⚠️ Sentiment analysis failed, using rating as fallback');
                }

                $em->persist($feedback);
                $em->flush();

                // Update doctor's average AI score
                $doctor = $feedback->getMedecin();
                $doctor->updateAiAverageScore();
                $em->flush();

                $this->addFlash('success', '✅ Thank you for your feedback!');
                return $this->redirectToRoute('app_my_appointments');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ Error saving feedback: ' . $e->getMessage());
                $logger->error('Error in feedback creation', [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            }
        }

        return $this->render('rendezvous/feedback.html.twig', [
            'feedback' => $feedback,
            'form' => $form,
            'rendezVous' => $rendezVous,
        ]);
    }

    #[Route('/doctor/{id}/feedbacks', name: 'app_doctor_feedbacks', methods: ['GET'])]
    public function doctorFeedbacks(
        Medecin $medecin,
        EntityManagerInterface $em
    ): Response
    {
        $feedbacks = $em->getRepository(Feedback::class)->findByDoctor($medecin->getId());

        return $this->render('feedback/doctor_feedbacks.html.twig', [
            'medecin' => $medecin,
            'feedbacks' => $feedbacks,
        ]);
    }
}