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
        $response = $this->client->request(
            'POST',
            'https://openrouter.ai/api/v1/chat/completions',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => 'openai/gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'user', 'content' => $message]
                    ]
                ]
            ]
        );

        $data = $response->toArray();

        return $data['choices'][0]['message']['content'] ?? 'No response';
    }
}
