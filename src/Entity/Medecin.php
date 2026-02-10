<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[UniqueEntity(fields: ['email'], message: 'This email is already used.')]
class Medecin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'First name is required.')]
    #[Assert\Length(max: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Last name is required.')]
    #[Assert\Length(max: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Specialty is required.')]
    #[Assert\Length(max: 100)]
    private ?string $specialite = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Email is required.')]
    #[Assert\Email(message: 'Please provide a valid email address.')]
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Phone number is required.')]
    #[Assert\Length(min: 6, max: 20)]
    private ?string $phone = null;

    #[ORM\OneToMany(mappedBy: 'doctor', targetEntity: RendezVous::class, cascade: ['remove'])]
    private Collection $rendezVous;

    #[ORM\OneToMany(mappedBy: 'medecin', targetEntity: Feedback::class, cascade: ['remove'])]
    private Collection $feedbacks;

    // ✅ NOUVEAUX CHAMPS AI
    #[ORM\Column(nullable: true)]
    private ?float $aiAverageScore = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $aiScoreUpdatedAt = null;

    public function __construct()
    {
        $this->rendezVous = new ArrayCollection();
        $this->feedbacks = new ArrayCollection();
    }

    // GETTERS & SETTERS EXISTANTS
    public function getId(): ?int { return $this->id; }
    public function getFirstName(): ?string { return $this->firstName; }
    public function setFirstName(string $firstName): self { $this->firstName = $firstName; return $this; }
    public function getLastName(): ?string { return $this->lastName; }
    public function setLastName(string $lastName): self { $this->lastName = $lastName; return $this; }
    public function getSpecialite(): ?string { return $this->specialite; }
    public function setSpecialite(string $specialite): self { $this->specialite = $specialite; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }
    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(string $phone): self { $this->phone = $phone; return $this; }

    public function getRendezVous(): Collection { return $this->rendezVous; }
    public function addRendezVous(RendezVous $rendezVous): self
    {
        if (!$this->rendezVous->contains($rendezVous)) {
            $this->rendezVous->add($rendezVous);
            $rendezVous->setDoctor($this);
        }
        return $this;
    }
    public function removeRendezVous(RendezVous $rendezVous): self
    {
        if ($this->rendezVous->removeElement($rendezVous)) {
            if ($rendezVous->getDoctor() === $this) {
                $rendezVous->setDoctor(null);
            }
        }
        return $this;
    }

    public function getFeedbacks(): Collection { return $this->feedbacks; }
    public function addFeedback(Feedback $feedback): self
    {
        if (!$this->feedbacks->contains($feedback)) {
            $this->feedbacks->add($feedback);
            $feedback->setMedecin($this);
        }
        return $this;
    }
    public function removeFeedback(Feedback $feedback): self
    {
        if ($this->feedbacks->removeElement($feedback)) {
            if ($feedback->getMedecin() === $this) {
                $feedback->setMedecin(null);
            }
        }
        return $this;
    }

    public function getAverageRating(): float
    {
        if ($this->feedbacks->isEmpty()) {
            return 0;
        }
        $total = 0;
        foreach ($this->feedbacks as $feedback) {
            $total += $feedback->getRating();
        }
        return round($total / $this->feedbacks->count(), 1);
    }

    // ✅ NOUVEAUX GETTERS/SETTERS AI
    public function getAiAverageScore(): ?float
    {
        return $this->aiAverageScore;
    }

    public function setAiAverageScore(?float $aiAverageScore): self
    {
        $this->aiAverageScore = $aiAverageScore;
        $this->aiScoreUpdatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getAiScoreUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->aiScoreUpdatedAt;
    }

    /**
     * Calculer et mettre à jour le score AI moyen
     */
    public function updateAiAverageScore(): void
    {
        $feedbacks = $this->feedbacks;
        
        if ($feedbacks->isEmpty()) {
            $this->aiAverageScore = null;
            $this->aiScoreUpdatedAt = null;
            return;
        }

        $total = 0;
        $count = 0;

        foreach ($feedbacks as $feedback) {
            if ($feedback->getSentimentScore() !== null) {
                $total += $feedback->getSentimentScore();
                $count++;
            }
        }

        if ($count > 0) {
            $this->aiAverageScore = round($total / $count, 2);
            $this->aiScoreUpdatedAt = new \DateTimeImmutable();
        } else {
            $this->aiAverageScore = null;
            $this->aiScoreUpdatedAt = null;
        }
    }

    public function __toString(): string { return 'Dr. ' . $this->firstName . ' ' . $this->lastName; }
}