<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class SentimentAnalysisService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private string $flaskUrl;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        // Flask API running on localhost:5000
        $this->flaskUrl = getenv('FLASK_API_URL') ?: 'http://localhost:5000';
    }

    /**
     * Analyze sentiment for a single feedback
     * 
     * @param string $comment The feedback comment
     * @param int $rating The rating (1-5)
     * @return array{
     *     success: bool,
     *     rating_score: float,
     *     textblob_score: float,
     *     vader_score: float,
     *     sentiment_score: float,
     *     final_score: float,
     *     sentiment_label: string,
     *     confidence: string
     * }|null
     */
    public function analyzeSentiment(string $comment, int $rating): ?array
    {
        try {
            $this->logger->info('🔍 Starting sentiment analysis', [
                'comment_length' => strlen($comment),
                'rating' => $rating,
                'flask_url' => $this->flaskUrl
            ]);

            $response = $this->httpClient->request('POST', $this->flaskUrl . '/analyze', [
                'json' => [
                    'comment' => $comment,
                    'rating' => $rating
                ],
                'timeout' => 10
            ]);

            $this->logger->info('Flask API response received', [
                'status_code' => $response->getStatusCode()
            ]);

            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();
                if ($data['success'] ?? false) {
                    $this->logger->info('✅ Sentiment analysis completed successfully', [
                        'final_score' => $data['data']['final_score'],
                        'sentiment_label' => $data['data']['sentiment_label']
                    ]);
                    return $data['data'];
                }
            }
            
            $this->logger->error('❌ Flask API error response', [
                'status' => $response->getStatusCode(),
                'body' => $response->getContent(false)
            ]);
            return null;

        } catch (\Exception $e) {
            $this->logger->error('❌ Sentiment analysis exception: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return null;
        }
    }

    /**
     * Analyze multiple feedbacks and get average score
     * 
     * @param array $feedbacks Array of ['comment' => string, 'rating' => int]
     * @return array{
     *     success: bool,
     *     feedbacks: array,
     *     average_score: float,
     *     total_count: int
     * }|null
     */
    public function analyzeBatch(array $feedbacks): ?array
    {
        try {
            $response = $this->httpClient->request('POST', $this->flaskUrl . '/analyze-batch', [
                'json' => ['feedbacks' => $feedbacks],
                'timeout' => 30
            ]);

            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();
                if ($data['success'] ?? false) {
                    $this->logger->debug('Batch sentiment analysis completed', [
                        'count' => $data['data']['total_count'],
                        'average' => $data['data']['average_score']
                    ]);
                    return $data['data'];
                }
            }

            $this->logger->error('Flask API batch error', [
                'status' => $response->getStatusCode()
            ]);
            return null;

        } catch (\Exception $e) {
            $this->logger->error('Batch sentiment analysis failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if Flask API is available
     */
    public function isHealthy(): bool
    {
        try {
            $response = $this->httpClient->request('GET', $this->flaskUrl . '/health', [
                'timeout' => 5
            ]);
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            $this->logger->warning('Flask API health check failed: ' . $e->getMessage());
            return false;
        }
    }
}
