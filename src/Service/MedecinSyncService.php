<?php

namespace App\Service;

use App\Entity\Medecin;
use App\Service\SentimentAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class MedecinSyncService
{
    private SentimentAnalysisService $sentimentService;
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(
        SentimentAnalysisService $sentimentService,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ) {
        $this->sentimentService = $sentimentService;
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * Synchronize a doctor's AI score by fetching all their feedbacks
     * and sending them to the Flask API.
     */
    public function syncAiScore(Medecin $doctor): bool
    {
        $feedbacks = $doctor->getFeedbacks();
        
        if ($feedbacks->isEmpty()) {
            $doctor->setAiAverageScore(null);
            $doctor->setAiScoreUpdatedAt(null);
            $this->em->flush();
            return true;
        }

        $feedbackData = [];
        foreach ($feedbacks as $fb) {
            $feedbackData[] = [
                'comment' => $fb->getComment(),
                'rating' => $fb->getRating()
            ];
        }

        $result = $this->sentimentService->getDoctorSentimentScore($feedbackData);

        if ($result && isset($result['average_sentiment_score'])) {
            $doctor->setAiAverageScore((float)$result['average_sentiment_score']);
            $doctor->setAiScoreUpdatedAt(new \DateTimeImmutable());
            $this->em->flush();
            
            $this->logger->info(' Doctor AI score synced', [
                'doctor_id' => $doctor->getId(),
                'score' => $result['average_sentiment_score']
            ]);
            return true;
        }

        $this->logger->error(' Flask API sync failed. Doctor AI score was NOT updated.', [
            'doctor_id' => $doctor->getId()
        ]);

        return false;
    }
}