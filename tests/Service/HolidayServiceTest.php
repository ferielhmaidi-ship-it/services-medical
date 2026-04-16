<?php

namespace App\Tests\Service;

use App\Service\HolidayService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Cache\CacheInterface;

class HolidayServiceTest extends TestCase
{
    private $httpClient;
    private $cache;
    private $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->service = new HolidayService($this->httpClient, $this->cache);
    }

    public function testGetTunisianHolidaysSuccess(): void
    {
        $year = 2024;
        $expectedHolidays = [
            ['date' => '2024-03-20', 'localName' => 'Independence Day', 'name' => 'Independence Day'],
        ];

        // Mock cache to execute the callback
        $this->cache->method('get')->willReturnCallback(function ($key, $callback) {
            $item = $this->createMock(\Symfony\Contracts\Cache\ItemInterface::class);
            return $callback($item);
        });

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn($expectedHolidays);

        $this->httpClient->method('request')->willReturn($response);

        $result = $this->service->getTunisianHolidays($year);

        $this->assertEquals($expectedHolidays, $result);
    }

    public function testGetTunisianHolidaysFailure(): void
    {
        $year = 2024;

        $this->cache->method('get')->willReturnCallback(function ($key, $callback) {
            $item = $this->createMock(\Symfony\Contracts\Cache\ItemInterface::class);
            return $callback($item);
        });

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(500);

        $this->httpClient->method('request')->willReturn($response);

        $result = $this->service->getTunisianHolidays($year);

        $this->assertEquals([], $result);
    }
}
