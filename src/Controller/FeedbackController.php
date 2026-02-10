<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\Patient;
use App\Form\FeedbackType;
use App\Repository\FeedbackRepository;
use App\Service\SentimentAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;

#[Route('/admin/feedback')]
final class FeedbackController extends AbstractController
{
    #[Route(name: 'app_feedback_index', methods: ['GET'])]
    public function index(FeedbackRepository $feedbackRepository): Response
    {
        return $this->render('feedback/index.html.twig', [
            'feedback' => $feedbackRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_feedback_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager,
        SentimentAnalysisService $sentimentService,
        LoggerInterface $logger
    ): Response
    {
        $feedback = new Feedback();
        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Analyze sentiment using Flask API
                $analysisResult = $sentimentService->analyzeSentiment(
                    $feedback->getComment(),
                    $feedback->getRating()
                );

                if ($analysisResult) {
                    $feedback->setSentimentScore((float)$analysisResult['final_score']);
                    $logger->info('Sentiment score calculated', [
                        'score' => $analysisResult['final_score'],
                        'label' => $analysisResult['sentiment_label']
                    ]);
                } else {
                    // Fallback: use rating if sentiment analysis fails
                    $feedback->setSentimentScore((float)$feedback->getRating());
                    $logger->warning('Sentiment analysis failed, using rating as fallback');
                }

                $entityManager->persist($feedback);
                $entityManager->flush();

                // Update doctor's average score
                $doctor = $feedback->getMedecin();
                $doctor->updateAiAverageScore();
                $entityManager->flush();

                $this->addFlash('success', '✅ Feedback added successfully!');
                return $this->redirectToRoute('app_feedback_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ Error creating feedback: ' . $e->getMessage());
                $logger->error('Error in feedback creation: ' . $e->getMessage());
            }
        }

        return $this->render('feedback/new.html.twig', [
            'feedback' => $feedback,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_feedback_show', methods: ['GET'])]
    public function show(Feedback $feedback): Response
    {
        return $this->render('feedback/show.html.twig', [
            'feedback' => $feedback,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_feedback_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Feedback $feedback, 
        EntityManagerInterface $entityManager,
        SentimentAnalysisService $sentimentService,
        LoggerInterface $logger
    ): Response
    {
        // Check if patient is the owner of this feedback
        $patientId = $request->getSession()->get('patient_id');
        if (!$patientId || $feedback->getPatient()->getId() !== $patientId) {
            $this->addFlash('error', 'You can only edit your own feedback.');
            return $this->redirectToRoute('app_patient_feedbacks');
        }

        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Re-analyze sentiment with updated comment/rating
                $analysisResult = $sentimentService->analyzeSentiment(
                    $feedback->getComment(),
                    $feedback->getRating()
                );

                if ($analysisResult) {
                    $feedback->setSentimentScore((float)$analysisResult['final_score']);
                    $logger->info('Sentiment score updated', [
                        'feedback_id' => $feedback->getId(),
                        'score' => $analysisResult['final_score']
                    ]);
                } else {
                    $feedback->setSentimentScore((float)$feedback->getRating());
                }

                $entityManager->flush();

                // Update doctor's average score
                $doctor = $feedback->getMedecin();
                $doctor->updateAiAverageScore();
                $entityManager->flush();

                $this->addFlash('success', 'Feedback updated successfully!');
                return $this->redirectToRoute('app_patient_feedbacks');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error updating feedback: ' . $e->getMessage());
                $logger->error('Error in feedback edit: ' . $e->getMessage());
            }
        }

        return $this->render('feedback/patient_edit.html.twig', [
            'feedback' => $feedback,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_feedback_delete', methods: ['POST'])]
    public function delete(Request $request, Feedback $feedback, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        // Check if patient is the owner of this feedback
        $patientId = $request->getSession()->get('patient_id');
        if (!$patientId || $feedback->getPatient()->getId() !== $patientId) {
            $this->addFlash('error', 'You can only delete your own feedback.');
            return $this->redirectToRoute('app_patient_feedbacks');
        }

        if ($this->isCsrfTokenValid('delete'.$feedback->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $doctor = $feedback->getMedecin();
                
                $entityManager->remove($feedback);
                $entityManager->flush();

                // Recalculate doctor's average score after deletion
                $doctor->updateAiAverageScore();
                $entityManager->flush();
                
                $this->addFlash('success', 'Feedback deleted successfully!');
                $logger->info('Feedback deleted, doctor score recalculated', [
                    'doctor_id' => $doctor->getId()
                ]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error deleting feedback: ' . $e->getMessage());
                $logger->error('Error in feedback deletion: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_patient_feedbacks');
    }

    #[Route('/patient/my-feedbacks', name: 'app_patient_feedbacks', methods: ['GET'])]
    public function myFeedbacks(Request $request, FeedbackRepository $feedbackRepository, EntityManagerInterface $em): Response
    {
        $patientId = $request->getSession()->get('patient_id');
        
        if (!$patientId) {
            $this->addFlash('error', 'Please log in by booking an appointment first.');
            return $this->redirectToRoute('app_appointment');
        }

        $patient = $em->getRepository(Patient::class)->find($patientId);
        if (!$patient) {
            $request->getSession()->remove('patient_id');
            $this->addFlash('error', 'Patient not found.');
            return $this->redirectToRoute('app_appointment');
        }

        $feedbacks = $feedbackRepository->findByPatient($patientId);

        return $this->render('feedback/patient_feedbacks.html.twig', [
            'patient' => $patient,
            'feedbacks' => $feedbacks,
        ]);
    }
}
