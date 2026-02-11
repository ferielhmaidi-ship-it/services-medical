<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'rapport')]
class Rapport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La raison de consultation est obligatoire.")]
    #[Assert\Length(max: 255, maxMessage: "La raison de consultation ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $consultationReason = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: "La date de création est obligatoire.")]
    #[Assert\Type(\DateTimeInterface::class)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le diagnostic est obligatoire.")]
    #[Assert\Length(max: 255)]
    private ?string $diagnosis = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "Les observations sont obligatoires.")]
    private ?string $observations = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "Les recommandations sont obligatoires.")]
    private ?string $recommendations = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "Les traitements sont obligatoires.")]
    private ?string $treatments = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: "La date de modification est obligatoire.")]
    #[Assert\Type(\DateTimeInterface::class)]
    private ?\DateTimeInterface $updatedAt = null;

    // ===================== RELATIONS =====================

    #[ORM\ManyToOne(targetEntity: Patient::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    #[ORM\ManyToOne(targetEntity: Medecin::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Medecin $medecin = null;

    #[ORM\ManyToOne(targetEntity: RendezVous::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?RendezVous $rendezVous = null;

    #[ORM\ManyToOne(targetEntity: Document::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Document $document = null;

    // ===================== GETTERS & SETTERS =====================

    public function getId(): ?int { return $this->id; }

    public function getConsultationReason(): ?string { return $this->consultationReason; }
    public function setConsultationReason(string $consultationReason): self { $this->consultationReason = $consultationReason; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getDiagnosis(): ?string { return $this->diagnosis; }
    public function setDiagnosis(string $diagnosis): self { $this->diagnosis = $diagnosis; return $this; }

    public function getObservations(): ?string { return $this->observations; }
    public function setObservations(string $observations): self { $this->observations = $observations; return $this; }

    public function getRecommendations(): ?string { return $this->recommendations; }
    public function setRecommendations(string $recommendations): self { $this->recommendations = $recommendations; return $this; }

    public function getTreatments(): ?string { return $this->treatments; }
    public function setTreatments(string $treatments): self { $this->treatments = $treatments; return $this; }

    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }

    public function getPatient(): ?Patient { return $this->patient; }
    public function setPatient(?Patient $patient): self { $this->patient = $patient; return $this; }

    public function getMedecin(): ?Medecin { return $this->medecin; }
    public function setMedecin(?Medecin $medecin): self { $this->medecin = $medecin; return $this; }

    public function getRendezVous(): ?RendezVous { return $this->rendezVous; }
    public function setRendezVous(?RendezVous $rendezVous): self { $this->rendezVous = $rendezVous; return $this; }

    public function getDocument(): ?Document { return $this->document; }
    public function setDocument(?Document $document): self { $this->document = $document; return $this; }
}
