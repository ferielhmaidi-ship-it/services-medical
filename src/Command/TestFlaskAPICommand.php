<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'sentiment:test-flask',
    description: 'Test Flask API connectivity and sentiment analysis'
)]
class TestFlaskAPICommand extends Command
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        parent::__construct();
        $this->httpClient = $httpClient;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $flaskUrl = getenv('FLASK_API_URL') ?: 'http://localhost:5000';
        
        $output->writeln([
            '',
            '🧪 Testing Flask Sentiment API',
            '═══════════════════════════════════════',
            "Flask URL: <info>$flaskUrl</info>",
            ''
        ]);

        // Test 1: Health check
        $output->writeln('<comment>[1/3] Testing Health Endpoint...</comment>');
        try {
            $response = $this->httpClient->request('GET', $flaskUrl . '/health', [
                'timeout' => 5
            ]);
            
            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();
                $output->writeln("✅ <info>Flask API is running!</info>");
                $output->writeln("   Response: " . json_encode($data));
            } else {
                $output->writeln("❌ <error>Unexpected status code: {$response->getStatusCode()}</error>");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $output->writeln("❌ <error>Health check failed: {$e->getMessage()}</error>");
            $output->writeln("   Make sure Flask is running: python flask-sentiment-api/app.py");
            return Command::FAILURE;
        }

        // Test 2: Single analysis
        $output->writeln('');
        $output->writeln('<comment>[2/3] Testing Single Analysis...</comment>');
        try {
            $response = $this->httpClient->request('POST', $flaskUrl . '/analyze', [
                'json' => [
                    'comment' => 'Excellent doctor, very professional!',
                    'rating' => 5
                ],
                'timeout' => 10
            ]);

            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();
                if ($data['success'] ?? false) {
                    $score = $data['data']['final_score'];
                    $label = $data['data']['sentiment_label'];
                    $output->writeln("✅ <info>Sentiment analysis successful!</info>");
                    $output->writeln("   Final Score: <info>$score</info>/5.0");
                    $output->writeln("   Sentiment Label: <info>$label</info>");
                } else {
                    $output->writeln("❌ <error>API returned success=false</error>");
                    $output->writeln("   Error: {$data['error']}");
                    return Command::FAILURE;
                }
            } else {
                $output->writeln("❌ <error>Status: {$response->getStatusCode()}</error>");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $output->writeln("❌ <error>Analysis failed: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }

        // Test 3: Batch analysis
        $output->writeln('');
        $output->writeln('<comment>[3/3] Testing Batch Analysis...</comment>');
        try {
            $response = $this->httpClient->request('POST', $flaskUrl . '/analyze-batch', [
                'json' => [
                    'feedbacks' => [
                        ['comment' => 'Great doctor!', 'rating' => 5],
                        ['comment' => 'Good service', 'rating' => 4],
                        ['comment' => 'Average', 'rating' => 3]
                    ]
                ],
                'timeout' => 30
            ]);

            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();
                if ($data['success'] ?? false) {
                    $avg = $data['data']['average_score'];
                    $count = $data['data']['total_count'];
                    $output->writeln("✅ <info>Batch analysis successful!</info>");
                    $output->writeln("   Average Score: <info>$avg</info>/5.0");
                    $output->writeln("   Total Feedbacks: <info>$count</info>");
                } else {
                    $output->writeln("❌ <error>Batch failed</error>");
                    return Command::FAILURE;
                }
            } else {
                $output->writeln("❌ <error>Status: {$response->getStatusCode()}</error>");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $output->writeln("❌ <error>Batch failed: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }

        $output->writeln([
            '',
            '═══════════════════════════════════════',
            '✅ <info>All tests passed!</info> Flask API is working correctly.',
            ''
        ]);
        
        return Command::SUCCESS;
    }
}
