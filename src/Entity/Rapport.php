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
    #[ORM\Column(name: 'idrapport', type: 'integer')]
    private ?int $idrapport = null;

    #[ORM\Column(name: 'consultation_reason', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "La raison de consultation est obligatoire.")]
    #[Assert\Length(max: 255, maxMessage: "La raison de consultation ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $consultation_reason = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Assert\NotNull(message: "La date de création est obligatoire.")]
    #[Assert\Type(\DateTimeInterface::class)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(name: 'diagnosis', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Le diagnostic est obligatoire.")]
    #[Assert\Length(max: 255)]
    private ?string $diagnosis = null;

    #[ORM\Column(name: 'observations', type: 'text')]
    #[Assert\NotBlank(message: "Les observations sont obligatoires.")]
    private ?string $observations = null;

    #[ORM\Column(name: 'recommendations', type: 'text')]
    #[Assert\NotBlank(message: "Les recommandations sont obligatoires.")]
    private ?string $recommendations = null;

    #[ORM\Column(name: 'treatments', type: 'text')]
    #[Assert\NotBlank(message: "Les traitements sont obligatoires.")]
    private ?string $treatments = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    #[Assert\NotNull(message: "La date de modification est obligatoire.")]
    #[Assert\Type(\DateTimeInterface::class)]
    private ?\DateTimeInterface $updated_at = null;

    // ===================== RELATIONS =====================

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

    public function getIdrapport(): ?int
    {
        return $this->idrapport;
    }

    public function getConsultationReason(): ?string
    {
        return $this->consultation_reason;
    }

    public function setConsultationReason(string $consultation_reason): self
    {
        $this->consultation_reason = $consultation_reason;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function setCreatedAtFromString(string $date): self
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $date);
        if ($dateTime === false) {
            throw new \InvalidArgumentException("Format de date invalide. Format attendu : YYYY-MM-DD HH:MM");
        }
        $this->created_at = $dateTime;
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

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(string $observations): self
    {
        $this->observations = $observations;
        return $this;
    }

    public function getRecommendations(): ?string
    {
        return $this->recommendations;
    }

    public function setRecommendations(string $recommendations): self
    {
        $this->recommendations = $recommendations;
        return $this;
    }

    public function getTreatments(): ?string
    {
        return $this->treatments;
    }

    public function setTreatments(string $treatments): self
    {
        $this->treatments = $treatments;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function setUpdatedAtFromString(string $date): self
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $date);
        if ($dateTime === false) {
            throw new \InvalidArgumentException("Format de date invalide. Format attendu : YYYY-MM-DD HH:MM");
        }
        $this->updated_at = $dateTime;
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
