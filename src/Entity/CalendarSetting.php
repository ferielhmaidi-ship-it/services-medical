<?php

namespace App\Entity;

use App\Repository\CalendarSettingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CalendarSettingRepository::class)]
class CalendarSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $slotDuration = 30;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $pauseStart = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $pauseEnd = null;

    #[ORM\Column]
    private ?int $doctorId = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlotDuration(): ?int
    {
        return $this->slotDuration;
    }

    public function setSlotDuration(int $slotDuration): static
    {
        $this->slotDuration = $slotDuration;

        return $this;
    }

    public function getPauseStart(): ?\DateTimeInterface
    {
        return $this->pauseStart;
    }

    public function setPauseStart(?\DateTimeInterface $pauseStart): static
    {
        $this->pauseStart = $pauseStart;

        return $this;
    }

    public function getPauseEnd(): ?\DateTimeInterface
    {
        return $this->pauseEnd;
    }

    public function setPauseEnd(?\DateTimeInterface $pauseEnd): static
    {
        $this->pauseEnd = $pauseEnd;

        return $this;
    }

    public function getDoctorId(): ?int
    {
        return $this->doctorId;
    }

    public function setDoctorId(int $doctorId): static
    {
        $this->doctorId = $doctorId;

        return $this;
    }
}