<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class TranslationService
{
    private const TRANSLATION_MODEL = 'openai/gpt-4o-mini';
    private const TRANSLATION_ENDPOINT = 'https://openrouter.ai/api/v1/chat/completions';

    private HttpClientInterface $client;
    private string $apiKey;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $client,
        LoggerInterface $logger,
        string $translationApiKey
    ) {
        $this->client = $client;
        $this->logger = $logger;
        $this->apiKey = $translationApiKey;
    }

    /**
     * Translates text to a target language.
     * Uses a chat-completions API and returns translated text only.
     */
    public function translate(string $text, string $targetLanguage): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        if (empty($this->apiKey)) {
            throw new \RuntimeException('Translation key is not configured.');
        }

        try {
            $response = $this->client->request(
                'POST',
                self::TRANSLATION_ENDPOINT,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type'  => 'application/json',
                        'HTTP-Referer' => 'http://127.0.0.1:8000',
                        'X-Title' => 'MediNest',
                    ],
                    'json' => [
                        'model' => self::TRANSLATION_MODEL,
                        'messages' => [
                            [
                                'role' => 'system', 
                                'content' => "You are a professional translator. Translate the given text to {$targetLanguage}. Output ONLY the translated text without any explanations or extra characters."
                            ],
                            ['role' => 'user', 'content' => $text]
                        ],
                        'temperature' => 0.1, // Low temperature for consistent translation
                    ]
                ]
            );

            if ($response->getStatusCode() !== 200) {
                $this->logger->error('Translation API error', [
                    'status' => $response->getStatusCode(),
                    'body' => $response->getContent(false),
                ]);
                throw new \RuntimeException('Translation provider rejected the request.');
            }

            $data = $response->toArray();
            $translated = trim((string) ($data['choices'][0]['message']['content'] ?? ''));
            if ($translated === '') {
                throw new \RuntimeException('Translation provider returned an empty response.');
            }

            return $translated;

        } catch (\Throwable $e) {
            $this->logger->error('Translation exception: ' . $e->getMessage());
            throw new \RuntimeException('Translation failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
