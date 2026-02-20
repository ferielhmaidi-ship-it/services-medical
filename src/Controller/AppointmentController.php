<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Feedback;
use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\RendezVous;
use App\Form\FeedbackType;
use App\Repository\AppointmentRepository;
use App\Repository\MedecinRepository;
use App\Repository\RendezVousRepository;
use App\Service\EmailService;
use App\Service\SentimentAnalysisService;
use App\Service\MedecinSyncService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AppointmentController extends AbstractController
{
    #[Route('/rendezvous/new/{medecin_id}', name: 'app_rendezvous_new', methods: ['GET', 'POST'])]
    public function new(int $medecin_id, Request $request, MedecinRepository $medecinRepo, EntityManagerInterface $em, EmailService $emailService): Response
    {
        $medecin = $medecinRepo->find($medecin_id);
        if (!$medecin) {
            throw $this->createNotFoundException('Médecin non trouvé.');
        }

        if ($request->isMethod('POST')) {
            $rendezVous = new RendezVous();
            $rendezVous->setPatient($this->getUser());
            $rendezVous->setDoctor($medecin);
            // Fix: RendezVous uses appointmentDate, not date/heure separately
            $dateTime = new \DateTime($request->request->get('date') . ' ' . $request->request->get('heure'));
            $rendezVous->setAppointmentDate($dateTime);
            $rendezVous->setStatut('en_attente');

            $em->persist($rendezVous);
            $em->flush();

            // Send confirmation email
            $user = $this->getUser();
            if ($user instanceof Patient) {
                try {
                    $emailService->sendRendezVousConfirmation($rendezVous, $user, $medecin);
                } catch (\Exception $e) {
                    // Log error safely
                }
            }

            return $this->redirectToRoute('app_patient_appointment_list');
        }

        return $this->render('rendezvous/new.html.twig', [
            'medecin' => $medecin,
        ]);
    }

    // Removed redundant/broken patientAppointments and doctorAppointments routes 
    // to avoid conflicts with BookingController and CalendarController
    
    #[Route('/rendezvous/{id}/status/{status}', name: 'app_rendezvous_update_status', methods: ['POST'])]
    public function updateStatus(RendezVous $rendezVous, string $status, EntityManagerInterface $em, EmailService $emailService): Response
    {
        $rendezVous->setStatut($status);
        $em->flush();

        if ($status === 'annule' || $status === 'cancelled') {
            try {
                $emailService->sendRendezVousCancellation($rendezVous, $rendezVous->getPatient(), $rendezVous->getDoctor());
            } catch (\Exception $e) {
                // Log error
            }
        }

        return $this->redirectToRoute('app_home'); // Default fallback
    }

    #[Route('/appointment/{id}/feedback', name: 'app_appointment_feedback', methods: ['GET', 'POST'])]
    public function addFeedback(
        RendezVous $rendezVous, 
        Request $request, 
        EntityManagerInterface $em, 
        SentimentAnalysisService $sentimentService, 
        MedecinSyncService $syncService,
        LoggerInterface $logger
    ): Response 
    {
        if ($rendezVous->getStatut() !== 'termine') {
            return $this->redirectToRoute('app_patient_appointment_list');
        }
        $feedback = new Feedback();
        $feedback->setPatient($rendezVous->getPatient());
        $feedback->setMedecin($rendezVous->getDoctor());
        $feedback->setRendezVous($rendezVous);
        
        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $analysisResult = $sentimentService->analyzeSentiment($feedback->getComment(), $feedback->getRating());
            $feedback->setSentimentScore($analysisResult ? (float)$analysisResult['final_score'] : (float)$feedback->getRating());
            
            $em->persist($feedback);
            $em->flush();
            
            $syncService->syncAiScore($feedback->getMedecin());
            
            return $this->redirectToRoute('app_patient_appointment_list');
        }
        
        return $this->render('rendezvous/feedback.html.twig', [
            'feedback' => $feedback,
            'form' => $form,
            'rendezVous' => $rendezVous,
        ]);
    }

    #[Route('/doctor/{id}/feedbacks', name: 'app_doctor_feedbacks', methods: ['GET'])]
    public function doctorFeedbacks(Medecin $medecin, EntityManagerInterface $em): Response
    {
        $feedbacks = $em->getRepository(Feedback::class)->findByDoctor($medecin->getId());
        return $this->render('feedback/doctor_feedbacks.html.twig', [
            'medecin' => $medecin,
            'feedbacks' => $feedbacks,
        ]);
    }
}
