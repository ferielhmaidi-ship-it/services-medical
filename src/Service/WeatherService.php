<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class WeatherService
{
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, CacheInterface $cache, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->apiKey = $apiKey;
    }

    /**
     * Checks if bad weather is forecasted for a given location and time.
     * Returns a description of the bad weather if found, null otherwise.
     */
    public function getBadWeatherForecast(string $location, \DateTimeInterface $dateTime): ?string
    {
        if (empty($this->apiKey) || $this->apiKey === 'YOUR_OPENWEATHER_API_KEY') {
            return null;
        }

        $forecastData = $this->cache->get("weather_forecast_" . urlencode($location), function (ItemInterface $item) use ($location) {
            $item->expiresAfter(3600); // Cache for 1 hour

            try {
                $response = $this->httpClient->request('GET', "https://api.openweathermap.org/data/2.5/forecast", [
                    'query' => [
                        'q' => $location . ',TN', // Specific to Tunisia as per governorates
                        'appid' => $this->apiKey,
                        'units' => 'metric',
                        'lang' => 'fr'
                    ]
                ]);

                if ($response->getStatusCode() !== 200) {
                    return null;
                }

                return $response->toArray();
            }
            catch (\Exception $e) {
                return null;
            }
        });

        if (!$forecastData || !isset($forecastData['list'])) {
            return null;
        }

        $targetTimestamp = $dateTime->getTimestamp();
        $closestForecast = null;
        $minDiff = PHP_INT_MAX;

        foreach ($forecastData['list'] as $forecast) {
            $diff = abs($forecast['dt'] - $targetTimestamp);
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closestForecast = $forecast;
            }
        }

        // Only consider forecasts within a 3-hour window
        if ($closestForecast && $minDiff < 5400) {
            $weather = $closestForecast['weather'][0] ?? null;
            if ($weather) {
                $main = $weather['main'];
                $description = $weather['description'];

                // Define "bad" weather: Thunderstorm, Drizzle, Rain, Snow, or specific descriptions
                $badCategories = ['Thunderstorm', 'Drizzle', 'Rain', 'Snow'];
                if (in_array($main, $badCategories)) {
                    return $description;
                }
            }
        }

        return null;
    }
}
