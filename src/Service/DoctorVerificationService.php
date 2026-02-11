<?php
// src/Service/DoctorVerificationService.php

namespace App\Service;

use App\Entity\Medecin;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;

class DoctorVerificationService
{
    private HttpClientInterface $httpClient;
    private string $flaskApiUrl;

    public function __construct(HttpClientInterface $httpClient, string $flaskApiUrl = 'http://localhost:5000')
    {
        $this->httpClient = $httpClient;
        $this->flaskApiUrl = $flaskApiUrl;
    }

    /**
     * Verify a doctor against the official directory.
     * Returns true if the doctor is found, false otherwise.
     */
    public function verify(Medecin $medecin): bool
    {
        try {
            $response = $this->httpClient->request('POST', $this->flaskApiUrl . '/search/doctors', [
                'json' => [
                    'name'       => $medecin->getFullName(),
                    'specialty'  => $medecin->getSpecialty(),
                    'governorate'=> $medecin->getGovernorate(),
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ],
                'timeout' => 30,
            ]);

            $data = $response->toArray();
            return $data['result'] ?? false;

        } catch (ClientException | ServerException | TransportException $e) {
            // Log the error if needed
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
