<?php

namespace App\Tests\Service;

use App\Service\SentimentAnalysisService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Psr\Log\LoggerInterface;

class SentimentAnalysisServiceTest extends TestCase
{
    private $httpClient;
    private $logger;
    private $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service = new SentimentAnalysisService($this->httpClient, $this->logger);
    }

    public function testAnalyzeSentimentSuccess(): void
    {
        $comment = "Excellent service!";
        $rating = 5;
        $expectedData = [
            'success' => true,
            'data' => [
                'final_score' => 0.9,
                'sentiment_label' => 'Positive',
                'confidence' => 'High'
            ]
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn($expectedData);

        $this->httpClient->method('request')->willReturn($response);

        $result = $this->service->analyzeSentiment($comment, $rating);

        $this->assertNotNull($result);
        $this->assertEquals('Positive', $result['sentiment_label']);
    }

    public function testAnalyzeSentimentFailure(): void
    {
        $comment = "Bad service";
        $rating = 1;

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(500);

        $this->httpClient->method('request')->willReturn($response);

        $result = $this->service->analyzeSentiment($comment, $rating);

        $this->assertNull($result);
    }
}
