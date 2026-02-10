<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'ordonnance')]
class Ordonnance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idordonnance', type: 'integer')]
    private ?int $idordonnance = null;

    #[ORM\Column(name: 'dateordonnance', type: 'datetime')]
    #[Assert\NotNull(message: "La date de l'ordonnance est obligatoire.")]
    #[Assert\Type(\DateTimeInterface::class)]
    private ?\DateTimeInterface $dateordonnance = null;

    #[ORM\Column(name: 'diagnosis', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Le diagnostic est obligatoire.")]
    #[Assert\Length(max: 255)]
    private ?string $diagnosis = null;

    #[ORM\Column(name: 'medicament', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Le médicament est obligatoire.")]
    #[Assert\Length(max: 255)]
    private ?string $medicament = null;

    #[ORM\Column(name: 'posologie', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "La posologie est obligatoire.")]
    #[Assert\Length(max: 255)]
    private ?string $posologie = null;

    #[ORM\Column(name: 'notes', type: 'text')]
    #[Assert\NotBlank(message: "Les notes sont obligatoires.")]
    private ?string $notes = null;

    #[ORM\Column(name: 'instructions', type: 'text')]
    #[Assert\NotBlank(message: "Les instructions sont obligatoires.")]
    private ?string $instructions = null;

    #[ORM\Column(name: 'createdAt', type: 'datetime')]
    #[Assert\NotNull(message: "La date de création est obligatoire.")]
    #[Assert\Type(\DateTimeInterface::class)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updatedAt', type: 'datetime')]
    #[Assert\NotNull(message: "La date de modification est obligatoire.")]
    #[Assert\Type(\DateTimeInterface::class)]
    private ?\DateTimeInterface $updatedAt = null;

    // ===================== RELATIONS =====================
    // PAS de validations côté serveur sur ces relations
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'idpatient', referencedColumnName: 'idpatient', nullable: false)]
    private ?Patient $idpatient = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'idmedecin', referencedColumnName: 'idmedecin', nullable: false)]
    private ?Medecin $idmedecin = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'idrendezvous', referencedColumnName: 'idrendezvous', nullable: false)]
    private ?Rendezvous $idrendezvous = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'iddocument', referencedColumnName: 'iddocument', nullable: true)]
    private ?Document $iddocument = null;

    // ===================== GETTERS & SETTERS =====================

    public function getIdordonnance(): ?int
    {
        return $this->idordonnance;
    }

    public function getDateordonnance(): ?\DateTimeInterface
    {
        return $this->dateordonnance;
    }

    public function setDateordonnance(\DateTimeInterface $dateordonnance): self
    {
        $this->dateordonnance = $dateordonnance;
        return $this;
    }

    public function setDateordonnanceFromString(string $date): self
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $date);
        if ($dateTime === false) {
            throw new \InvalidArgumentException("Format de date invalide. Format attendu : YYYY-MM-DD HH:MM");
        }
        $this->dateordonnance = $dateTime;
        return $this;
    }

    public function getDiagnosis(): ?string
    {
        return $this->diagnosis;
    }

    public function setDiagnosis(string $diagnosis): self
    {
        $this->diagnosis = $diagnosis;
        return $this;
    }

    public function getMedicament(): ?string
    {
        return $this->medicament;
    }

    public function setMedicament(string $medicament): self
    {
        $this->medicament = $medicament;
        return $this;
    }

    public function getPosologie(): ?string
    {
        return $this->posologie;
    }

    public function setPosologie(string $posologie): self
    {
        $this->posologie = $posologie;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(string $instructions): self
    {
        $this->instructions = $instructions;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setCreatedAtFromString(string $date): self
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $date);
        if ($dateTime === false) {
            throw new \InvalidArgumentException("Format de date invalide. Format attendu : YYYY-MM-DD HH:MM");
        }
        $this->createdAt = $dateTime;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function setUpdatedAtFromString(string $date): self
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $date);
        if ($dateTime === false) {
            throw new \InvalidArgumentException("Format de date invalide. Format attendu : YYYY-MM-DD HH:MM");
        }
        $this->updatedAt = $dateTime;
        return $this;
    }

    public function getIdpatient(): ?Patient
    {
        return $this->idpatient;
    }

    public function setIdpatient(?Patient $idpatient): self
    {
        $this->idpatient = $idpatient;
        return $this;
    }

    public function getIdmedecin(): ?Medecin
    {
        return $this->idmedecin;
    }

    public function setIdmedecin(?Medecin $idmedecin): self
    {
        $this->idmedecin = $idmedecin;
        return $this;
    }

    public function getIdrendezvous(): ?Rendezvous
    {
        return $this->idrendezvous;
    }

    public function setIdrendezvous(?Rendezvous $idrendezvous): self
    {
        $this->idrendezvous = $idrendezvous;
        return $this;
    }

    public function getIddocument(): ?Document
    {
        return $this->iddocument;
    }

    public function setIddocument(?Document $iddocument): self
    {
        $this->iddocument = $iddocument;
        return $this;
    }
}
