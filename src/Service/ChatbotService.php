<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatbotService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(
        HttpClientInterface $client,
        string $openRouterApiKey
    ) {
        $this->client = $client;
        $this->apiKey = $openRouterApiKey;
    }

    public function ask(string $message): string
    {
        try {
            $response = $this->client->request(
                'POST',
                'https://openrouter.ai/api/v1/chat/completions',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type'  => 'application/json',
                        'HTTP-Referer'  => 'https://github.com/symfony/symfony', // Optional for OpenRouter
                        'X-Title'       => 'Symfony App', // Optional for OpenRouter
                    ],
                    'json' => [
                        'model' => 'openai/gpt-3.5-turbo',
                        'messages' => [
                            ['role' => 'user', 'content' => $message]
                        ]
                    ]
                ]
            );

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                return 'Error: API returned status ' . $statusCode;
            }

            $data = $response->toArray();
            return $data['choices'][0]['message']['content'] ?? 'No response';

        } catch (\Throwable $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function isSafe(string $text): bool
    {
        if (empty(trim($text))) {
            return true;
        }

        $prompt = "Analyse le texte suivant et réponds uniquement par 'SAFE' s'il est approprié ou 'UNSAFE' s'il contient des insultes, du harcèlement ou un langage inapproprié (sois attentif aux mots comme 'grosse' s'ils sont utilisés comme insulte, mais accepte-les s'ils sont dans un contexte médical ou descriptif neutre) :\n\n" . $text;

        $response = $this->ask($prompt);
        
        return trim(strtoupper($response)) === 'SAFE';
    }
}
