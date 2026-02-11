<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Patient;
use App\Entity\Medecin;
use App\Entity\RendezVous;
use App\Entity\Document;

#[ORM\Entity]
#[ORM\Table(name: 'ordonnances')]
class Ordonnance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: "La date de l'ordonnance est obligatoire.")]
    private ?\DateTimeInterface $dateOrdonnance = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Le diagnostic est obligatoire.")]
    private ?string $diagnosis = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Le médicament est obligatoire.")]
    private ?string $medicament = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "La posologie est obligatoire.")]
    private ?string $posologie = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "Les notes sont obligatoires.")]
    private ?string $notes = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "Les instructions sont obligatoires.")]
    private ?string $instructions = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

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

    // ===================== CONSTRUCT =====================
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    // ===================== GETTERS & SETTERS =====================
    public function getId(): ?int { return $this->id; }
    public function getDateOrdonnance(): ?\DateTimeInterface { return $this->dateOrdonnance; }
    public function setDateOrdonnance(\DateTimeInterface $dateOrdonnance): self { $this->dateOrdonnance = $dateOrdonnance; return $this; }

    public function getDiagnosis(): ?string { return $this->diagnosis; }
    public function setDiagnosis(string $diagnosis): self { $this->diagnosis = $diagnosis; return $this; }

    public function getMedicament(): ?string { return $this->medicament; }
    public function setMedicament(string $medicament): self { $this->medicament = $medicament; return $this; }

    public function getPosologie(): ?string { return $this->posologie; }
    public function setPosologie(string $posologie): self { $this->posologie = $posologie; return $this; }

    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(string $notes): self { $this->notes = $notes; return $this; }

    public function getInstructions(): ?string { return $this->instructions; }
    public function setInstructions(string $instructions): self { $this->instructions = $instructions; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }

    public function getPatient(): ?Patient { return $this->patient; }
    public function setPatient(?Patient $patient): self { $this->patient = $patient; return $this; }

    public function getMedecin(): ?Medecin { return $this->medecin; }
    public function setMedecin(?Medecin $medecin): self { $this->medecin = $medecin; return $this; }

    public function getRendezVous(): ?RendezVous { return $this->rendezVous; }
    public function setRendezVous(?RendezVous $rendezVous): self { $this->rendezVous = $rendezVous; return $this; }

    public function getDocument(): ?Document { return $this->document; }
    public function setDocument(?Document $document): self { $this->document = $document; return $this; }
}
