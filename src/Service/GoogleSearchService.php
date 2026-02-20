<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleSearchService
{
    private HttpClientInterface $client;
    private string $apiKey;
    private string $searchEngineId;

    public function __construct(HttpClientInterface $client, string $googleApiKey = '', string $searchEngineId = '')
    {
        $this->client = $client;
        $this->apiKey = $googleApiKey;
        $this->searchEngineId = $searchEngineId;
    }

    public function search(string $query): string
    {
        if (empty($this->apiKey) || empty($this->searchEngineId)) {
            return "Recherche Google non configurée. Veuillez configurer GOOGLE_API_KEY et GOOGLE_SEARCH_ENGINE_ID.";
        }

        try {
            $response = $this->client->request('GET', 'https://www.googleapis.com/customsearch/v1', [
                'query' => [
                    'key' => $this->apiKey,
                    'cx' => $this->searchEngineId,
                    'q' => $query,
                    'num' => 3, // Nombre de résultats
                    'lr' => 'lang_fr', // Résultats en français
                ],
                'timeout' => 10
            ]);

            $data = $response->toArray();

            if (!isset($data['items'])) {
                return "Aucun résultat trouvé sur Google.";
            }

            $results = "Résultats de recherche Google :\n\n";
            foreach ($data['items'] as $index => $item) {
                $results .= ($index + 1) . ". " . $item['title'] . "\n";
                $results .= "   " . ($item['snippet'] ?? 'Pas de description') . "\n";
                $results .= "   Source: " . $item['link'] . "\n\n";
            }

            return $results;

        } catch (\Exception $e) {
            return "Erreur lors de la recherche Google : " . $e->getMessage();
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->searchEngineId);
    }
}