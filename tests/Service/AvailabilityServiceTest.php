<?php

namespace App\Tests\Service;

use App\Entity\Medecin;
use App\Entity\Appointment;
use App\Repository\IndisponibiliteRepository;
use App\Repository\TempsTravailRepository;
use App\Repository\AppointmentRepository;
use App\Service\AvailabilityService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AvailabilityServiceTest extends TestCase
{
    private $ttRepo;
    private $indispRepo;
    private $apptRepo;
    private $logger;
    private $service;

    protected function setUp(): void
    {
        $this->ttRepo = $this->createMock(TempsTravailRepository::class);
        $this->indispRepo = $this->createMock(IndisponibiliteRepository::class);
        $this->apptRepo = $this->createMock(AppointmentRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new AvailabilityService(
            $this->ttRepo,
            $this->indispRepo,
            $this->apptRepo,
            $this->logger
            );
    }

    public function testGetWeeklyWorkingDays(): void
    {
        $doctor = $this->createMock(Medecin::class);
        $doctor->method('getId')->willReturn(1);

        // Use a future Monday to avoid "past slots" logic if any
        $startDate = new \DateTime('2028-03-06'); // A Monday in the future

        $this->ttRepo->method('findBy')->willReturn([]);
        $this->indispRepo->method('findBy')->willReturn([]);

        $result = $this->service->getWeeklyWorkingDays($doctor, $startDate);

        // By default, if no config, it returns Monday-Friday (fallback)
        $this->assertCount(5, $result);
        $this->assertEquals('Lun : 06 mars', $result[0]);
    }

    public function testGetAvailableSlotsEmpty(): void
    {
        $doctor = $this->createMock(Medecin::class);
        $doctor->method('getId')->willReturn(1);
        $startDate = new \DateTime('2028-03-06');

        $this->ttRepo->method('findBy')->willReturn([]);
        $this->indispRepo->method('findBy')->willReturn([]);
        $this->apptRepo->method('findByDoctorAndRange')->willReturn([]);

        $result = $this->service->getAvailableSlots($doctor, $startDate, 1);

        $this->assertNotEmpty($result);
        $this->assertEquals('2028-03-06', $result[0]['date']);
        $this->assertContains('09:00', $result[0]['slots']);
    }
}
