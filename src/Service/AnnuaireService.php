<?php
// src/Service/AnnuaireService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;

class AnnuaireService
{
    private $httpClient;
    private $flaskApiUrl;

     public function __construct(HttpClientInterface $httpClient, string $flaskApiUrl = 'http://localhost:5000')
    {
        $this->httpClient = $httpClient;
        $this->flaskApiUrl = $flaskApiUrl;
    }

    /**
     * Search doctors in the medical directory with pagination
     */
    public function searchDoctors(array $criteria = [], int $page = 1, int $perPage = 20): array
    {
        try {
            // Ensure criteria is always an associative array (object in JSON)
            $jsonData = empty($criteria) ? (object)[] : $criteria;

            $response = $this->httpClient->request('POST', $this->flaskApiUrl . '/search/doctorsList', [
                'json' => $jsonData,
                'query' => [
                    'page' => $page,
                    'size' => $perPage,
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            $data = $response->toArray();

            return [
                'success' => true,
                'doctors' => $data['doctors'] ?? [],
                'pagination' => [
                    'currentPage' => $data['currentPage'] ?? $page,
                    'pageSize' => $data['pageSize'] ?? $perPage,
                    'totalItems' => $data['totalItems'] ?? 0,
                    'totalPages' => $data['totalPages'] ?? 0,
                ],
                'message' => 'Recherche effectuée avec succès',
            ];

        } catch (ClientException $e) {
            return [
                'success' => false,
                'message' => 'Erreur de connexion au service',
                'error' => $e->getMessage(),
                'doctors' => [],
                'pagination' => [
                    'currentPage' => $page,
                    'pageSize' => $perPage,
                    'totalItems' => 0,
                    'totalPages' => 0,
                ],
            ];
        } catch (ServerException $e) {
            return [
                'success' => false,
                'message' => 'Service temporairement indisponible',
                'error' => $e->getMessage(),
                'doctors' => [],
                'pagination' => [
                    'currentPage' => $page,
                    'pageSize' => $perPage,
                    'totalItems' => 0,
                    'totalPages' => 0,
                ],
            ];
        } catch (TransportException $e) {
            return [
                'success' => false,
                'message' => 'Impossible de contacter le service',
                'error' => $e->getMessage(),
                'doctors' => [],
                'pagination' => [
                    'currentPage' => $page,
                    'pageSize' => $perPage,
                    'totalItems' => 0,
                    'totalPages' => 0,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur inattendue',
                'error' => $e->getMessage(),
                'doctors' => [],
                'pagination' => [
                    'currentPage' => $page,
                    'pageSize' => $perPage,
                    'totalItems' => 0,
                    'totalPages' => 0,
                ],
            ];
        }
    }

    /**
     * Get API status
     */
    public function getApiStatus(): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->flaskApiUrl . '/', [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'timeout' => 10,
            ]);

            return $response->toArray();

        } catch (\Exception $e) {
            return [
                'status' => 'ERROR',
                'message' => 'Service non disponible',
                'data_loaded' => false,
                'record_count' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
}
