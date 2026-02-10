<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\RendezVousRepository;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
#[ORM\Table(name: 'rendez_vous')]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'Please select an appointment date.')]
    #[Assert\Type(type: \DateTimeInterface::class, message: 'Invalid date format.')]
    #[Assert\GreaterThan('now', message: 'Appointment date must be in the future.')]
    private ?\DateTimeInterface $appointmentDate = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Length(max: 500, maxMessage: 'Message cannot exceed {{ limit }} characters.')]
    private ?string $message = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: ['en_attente', 'termine', 'annule'], message: 'Invalid status.')]
    private ?string $statut = 'en_attente';

    #[ORM\ManyToOne(targetEntity: Medecin::class, inversedBy: 'rendezVous')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Please select a doctor.')]
    private ?Medecin $doctor = null;

    #[ORM\ManyToOne(targetEntity: Patient::class, inversedBy: 'rendezVous')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Please select a patient.')]
    private ?Patient $patient = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->statut = 'en_attente';
    }

    // GETTERS & SETTERS
    public function getId(): ?int { return $this->id; }
    public function getAppointmentDate(): ?\DateTimeInterface { return $this->appointmentDate; }
    public function setAppointmentDate(?\DateTimeInterface $appointmentDate): self { $this->appointmentDate = $appointmentDate; return $this; }
    public function getMessage(): ?string { return $this->message; }
    public function setMessage(?string $message): self { $this->message = $message; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): self { $this->statut = $statut; return $this; }
    public function getDoctor(): ?Medecin { return $this->doctor; }
    public function setDoctor(?Medecin $doctor): self { $this->doctor = $doctor; return $this; }
    public function getPatient(): ?Patient { return $this->patient; }
    public function setPatient(?Patient $patient): self { $this->patient = $patient; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }
}