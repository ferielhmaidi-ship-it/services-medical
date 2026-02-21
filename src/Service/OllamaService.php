<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OllamaService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $openRouterApiKey)
    {
        $this->client = $client;
        $this->apiKey = $openRouterApiKey;
    }

    public function ask(string $prompt): string
    {
        try {
            $response = $this->client->request(
                'POST',
                'https://openrouter.ai/api/v1/chat/completions',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type'  => 'application/json',
                        'HTTP-Referer'  => 'http://localhost', // Optional/required for OpenRouter free
                        'X-Title'       => 'Symfony TabibNet',
                    ],
                    'json' => [
                        'model' => 'openrouter/auto',
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt]
                        ],
                    ],
                    'timeout' => 90
                ]
            );

            if ($response->getStatusCode() !== 200) {
                return 'Erreur API OpenRouter (Status ' . $response->getStatusCode() . ')';
            }

            $data = $response->toArray(false);

            return $data['choices'][0]['message']['content'] ?? 'Aucune réponse.';
        } catch (\Throwable $e) {
            return 'Erreur de connexion IA : ' . $e->getMessage();
        }
    }
}
