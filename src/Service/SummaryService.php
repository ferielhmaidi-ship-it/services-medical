<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SummaryService
{
    private const GROQ_MODEL = 'meta-llama/llama-4-scout-17b-16e-instruct';
    private const GROQ_ENDPOINT = 'https://api.groq.com/openai/v1/chat/completions';
    private const OPENROUTER_MODEL = 'openai/gpt-4o-mini';
    private const OPENROUTER_ENDPOINT = 'https://openrouter.ai/api/v1/chat/completions';

    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(
        HttpClientInterface $httpClient,
        ?string $groqApiKey = null,
        ?string $translationApiKey = null,
        ?string $openrouterApiKey = null
    ) {
        $this->httpClient = $httpClient;
        $this->apiKey = trim((string) $groqApiKey)
            ?: trim((string) $translationApiKey)
            ?: trim((string) $openrouterApiKey);
    }

    public function summarize(string $text, string $scope = 'article'): string
    {
        $content = trim(strip_tags($text));
        if ($content === '') {
            throw new \RuntimeException('No content available for summary.');
        }

        if ($this->apiKey === '') {
            throw new \RuntimeException('No API key configured for summary.');
        }

        $promptPrefix = $scope === 'magazine'
            ? 'Fais un resume clair et structure de cette description de magazine :'
            : 'Fais un resume clair et structure du texte suivant :';

        $isOpenRouterKey = str_starts_with($this->apiKey, 'sk-or-');
        $endpoint = $isOpenRouterKey ? self::OPENROUTER_ENDPOINT : self::GROQ_ENDPOINT;
        $model = $isOpenRouterKey ? self::OPENROUTER_MODEL : self::GROQ_MODEL;
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        if ($isOpenRouterKey) {
            $headers['HTTP-Referer'] = 'http://127.0.0.1:8000';
            $headers['X-Title'] = 'MediNest';
        }

        $response = $this->httpClient->request('POST', $endpoint, [
            'headers' => $headers,
            'json' => [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $promptPrefix . "\n\n" . $content,
                    ],
                ],
                'temperature' => 0.3,
                'max_tokens' => 400,
            ],
        ]);

        $status = $response->getStatusCode();
        $data = $response->toArray(false);
        if ($status !== 200) {
            $details = $data['error']['message'] ?? 'Summary API rejected the request.';
            throw new \RuntimeException($details);
        }

        $summary = trim((string) ($data['choices'][0]['message']['content'] ?? ''));
        if ($summary === '') {
            throw new \RuntimeException('Empty summary from API.');
        }

        return $summary;
    }
}
