<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class DoctorRagService
{
    // Flask service base URL — update this when you deploy
    private string $flaskBaseUrl;

    public function __construct(
        private HttpClientInterface $httpClient,
        string $flaskBaseUrl = 'http://localhost:5000'
    ) {
        $this->flaskBaseUrl = rtrim($flaskBaseUrl, '/');
    }

    /**
     * Send a question to the Flask RAG service and return the structured response.
     *
     * @param string $question  The user's question (text from speech-to-text or typed)
     * @param int    $topK      Number of doctors to retrieve (default 8)
     *
     * @return array{
     *   status: string,
     *   responseSentence: string|null,
     *   doctors: array,
     *   notes: array,
     *   scores: array,
     *   message: string|null
     * }
     */
    public function query(string $question, int $topK = 8): array
    {
        try {
            $response = $this->httpClient->request('POST', $this->flaskBaseUrl . '/query', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ],
                'json' => [
                    'question' => $question,
                    'top_k'    => $topK,
                ],
                'timeout' => 30, // seconds — GPT-4o can be slow
            ]);

            $data = $response->toArray(throw: false);

            return $this->normalizeResponse($data, $response->getStatusCode());

        } catch (TransportExceptionInterface $e) {
            // Flask service is unreachable (not running, wrong URL, etc.)
            return $this->errorResponse('Le service médical est temporairement indisponible. Veuillez réessayer.');
        } catch (ClientExceptionInterface | ServerExceptionInterface $e) {
            return $this->errorResponse('Une erreur est survenue lors de la communication avec le service médical.');
        } catch (\Exception $e) {
            return $this->errorResponse('Une erreur inattendue est survenue.');
        }
    }

    /**
     * Check if the Flask service is up and running.
     */
    public function isHealthy(): bool
    {
        try {
            $response = $this->httpClient->request('GET', $this->flaskBaseUrl . '/health', [
                'timeout' => 5,
            ]);
            $data = $response->toArray(throw: false);
            return ($data['status'] ?? '') === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get index stats (total records, by governorate, top specialties).
     */
    public function getStats(): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->flaskBaseUrl . '/stats', [
                'timeout' => 5,
            ]);
            return $response->toArray(throw: false);
        } catch (\Exception $e) {
            return [];
        }
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Normalize Flask response into a consistent structure.
     */
    private function normalizeResponse(array $data, int $statusCode): array
    {
        $status = $data['status'] ?? 'error';

        return match($status) {
            'success' => [
                'status'           => 'success',
                'responseSentence' => $data['responseSentence'] ?? null,
                'doctors'          => $data['doctors'] ?? [],
                'notes'            => $data['notes'] ?? [],
                'scores'           => $data['scores'] ?? [],
                'message'          => null,
            ],
            'greeting' => [
                'status'           => 'greeting',
                'responseSentence' => null,
                'doctors'          => [],
                'notes'            => [],
                'scores'           => [],
                'message'          => $data['message'] ?? null,
            ],
            'insufficient_context' => [
                'status'           => 'insufficient_context',
                'responseSentence' => null,
                'doctors'          => [],
                'notes'            => [],
                'scores'           => [],
                'message'          => $data['message'] ?? 'Aucune information trouvée.',
            ],
            default => $this->errorResponse($data['message'] ?? 'Une erreur est survenue.'),
        };
    }

    private function errorResponse(string $message): array
    {
        return [
            'status'           => 'error',
            'responseSentence' => null,
            'doctors'          => [],
            'notes'            => [],
            'scores'           => [],
            'message'          => $message,
        ];
    }
}
