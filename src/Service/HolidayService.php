<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class HolidayService
{
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;

    public function __construct(HttpClientInterface $httpClient, CacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    public function getTunisianHolidays(int $year): array
    {
        return $this->cache->get("tunisian_holidays_$year", function (ItemInterface $item) use ($year) {
            $item->expiresAfter(3600 * 24 * 7); // Cache for 1 week

            try {
                $response = $this->httpClient->request(
                    'GET',
                    "https://date.nager.at/api/v3/PublicHolidays/$year/TN"
                );

                if ($response->getStatusCode() !== 200) {
                    return [];
                }

                return $response->toArray();
            }
            catch (\Exception $e) {
                return [];
            }
        });
    }
}
