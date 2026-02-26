<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\Appointment;
use App\Entity\Patient;
use App\Repository\MedecinRepository;
use App\Repository\AppointmentRepository;
use App\Repository\FeedbackRepository;
use App\Service\SentimentAnalysisService;
use App\Service\MedecinSyncService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/appointment/feedback')]
class AppointmentFeedbackController extends AbstractController
{
    #[Route('/new/{id}', name: 'app_appointment_feedback_new')]
    public function newForAppointment(
        int $id,
        Request $request,
        AppointmentRepository $apptRepo,
        MedecinRepository $medecinRepo,
        FeedbackRepository $feedbackRepo,
        EntityManagerInterface $em,
        SentimentAnalysisService $sentimentService,
        MedecinSyncService $syncService
    ): Response {
        $user = $this->getUser();
        if (!$user || !($user instanceof Patient)) {
            return $this->redirectToRoute('app_login');
        }

        $appointment = $apptRepo->find($id);
        if (!$appointment || $appointment->getPatient()->getId() !== $user->getId()) {
            $this->addFlash('error', 'Rendez-vous non trouvé.');
            return $this->redirectToRoute('app_patient_appointment_list');
        }

        if ($appointment->getStatus() !== 'completed') {
            $this->addFlash('error', 'Vous ne pouvez donner un avis que pour un rendez-vous terminé.');
            return $this->redirectToRoute('app_patient_appointment_list');
        }

        $existingFeedback = $feedbackRepo->findOneBy(['appointment' => $appointment]);
        if ($existingFeedback) {
            return $this->redirectToRoute('app_appointment_feedback_manage', ['id' => $existingFeedback->getId()]);
        }

        $doctor = $appointment->getDoctor();
        
        if ($request->isMethod('POST')) {
            $rating = (int) $request->request->get('rating');
            $comment = $request->request->get('comment');

            if ($rating < 1 || $rating > 5) {
                $this->addFlash('error', 'La note doit être entre 1 et 5.');
            } elseif (strlen($comment) < 10) {
                $this->addFlash('error', 'Le commentaire doit contenir au moins 10 caractères.');
            } else {
                $feedback = new Feedback();
                $feedback->setRating($rating);
                $feedback->setComment($comment);
                $feedback->setPatient($user);
                $feedback->setMedecin($doctor);
                $feedback->setAppointment($appointment);
                
                $analysisResult = $sentimentService->analyzeSentiment($comment, $rating);
                $feedback->setSentimentScore($analysisResult ? (float)$analysisResult['final_score'] : (float)$rating);

                $em->persist($feedback);
                $em->flush();

                $syncService->syncAiScore($doctor);

                $this->addFlash('success', 'Merci pour votre avis !');
                return $this->redirectToRoute('app_patient_appointment_list');
            }
        }

        return $this->render('patient_feedback/new_for_appointment.html.twig', [
            'appointment' => $appointment,
            'doctor' => $doctor,
            'is_edit' => false
        ]);
    }

    #[Route('/manage/{id}', name: 'app_appointment_feedback_manage')]
    public function manageFeedback(int $id, FeedbackRepository $feedbackRepo): Response
    {
        $user = $this->getUser();
        $feedback = $feedbackRepo->find($id);

        if (!$feedback || $feedback->getPatient() !== $user) {
            $this->addFlash('error', 'Avis non trouvé.');
            return $this->redirectToRoute('app_patient_appointment_list');
        }

        return $this->render('patient_feedback/manage.html.twig', [
            'feedback' => $feedback,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_appointment_feedback_edit')]
    public function editFeedback(
        int $id,
        Request $request,
        FeedbackRepository $feedbackRepo,
        EntityManagerInterface $em,
        SentimentAnalysisService $sentimentService,
        MedecinSyncService $syncService
    ): Response {
        $user = $this->getUser();
        $feedback = $feedbackRepo->find($id);

        if (!$feedback || $feedback->getPatient() !== $user) {
            $this->addFlash('error', 'Avis non trouvé.');
            return $this->redirectToRoute('app_patient_appointment_list');
        }

        if ($request->isMethod('POST')) {
            $rating = (int) $request->request->get('rating');
            $comment = $request->request->get('comment');

            if ($rating < 1 || $rating > 5) {
                $this->addFlash('error', 'La note doit être entre 1 et 5.');
            } elseif (strlen($comment) < 10) {
                $this->addFlash('error', 'Le commentaire doit contenir au moins 10 caractères.');
            } else {
                $feedback->setRating($rating);
                $feedback->setComment($comment);
                
                $analysisResult = $sentimentService->analyzeSentiment($comment, $rating);
                $feedback->setSentimentScore($analysisResult ? (float)$analysisResult['final_score'] : (float)$rating);

                $em->flush();

                $syncService->syncAiScore($feedback->getMedecin());

                $this->addFlash('success', 'Votre avis a été mis à jour.');
                return $this->redirectToRoute('app_patient_appointment_list');
            }
        }

        return $this->render('patient_feedback/new_for_appointment.html.twig', [
            'appointment' => $feedback->getAppointment(),
            'doctor' => $feedback->getMedecin(),
            'feedback' => $feedback,
            'is_edit' => true
        ]);
    }

    #[Route('/delete/{id}', name: 'app_appointment_feedback_delete', methods: ['POST'])]
    public function deleteFeedback(int $id, FeedbackRepository $feedbackRepo, EntityManagerInterface $em, MedecinSyncService $syncService): Response
    {
        $user = $this->getUser();
        $feedback = $feedbackRepo->find($id);

        if (!$feedback || $feedback->getPatient() !== $user) {
            $this->addFlash('error', 'Avis non trouvé.');
            return $this->redirectToRoute('app_patient_appointment_list');
        }

        $doctor = $feedback->getMedecin();
        $em->remove($feedback);
        $em->flush();

        if ($doctor) {
            $syncService->syncAiScore($doctor);
        }

        $this->addFlash('success', 'Votre avis a été supprimé.');
        return $this->redirectToRoute('app_patient_appointment_list');
    }
}