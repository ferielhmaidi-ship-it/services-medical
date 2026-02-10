<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OllamaService
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function ask(string $prompt): string
    {
        try {
            $response = $this->client->request(
                'POST',
                'http://localhost:11434/api/generate',
                [
                    'json' => [
                        'model' => 'llama3',
                        'prompt' => $prompt,
                        'stream' => false
                    ],
                    'timeout' => 60
                ]
            );

            $data = $response->toArray(false);

            return $data['response'] ?? 'Aucune réponse.';
        } catch (\Throwable $e) {
            return 'Erreur Ollama : ' . $e->getMessage();
        }
    }
}
