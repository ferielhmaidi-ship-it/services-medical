<?php

namespace App\Entity;

use App\Repository\MedecinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: MedecinRepository::class)]
#[ORM\Table(name: 'medecins')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['cin'], message: 'This CIN is already registered.')]
class Medecin extends BaseUser
{
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $specialty;

    #[ORM\Column(type: 'string', length: 8, unique: true)]
    private string $cin;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $governorate = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $education = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $experience = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isVerified = false;

    #[ORM\Column(type: 'float', nullable: true, name: 'ai_average_score')]
    private ?float $aiAverageScore = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true, name: 'ai_score_updated_at')]
    private ?\DateTimeImmutable $aiScoreUpdatedAt = null;

    /** @var Collection<int, RendezVous> */
    #[ORM\OneToMany(mappedBy: 'doctor', targetEntity: RendezVous::class)]
    private Collection $rendezVous;

    /** @var Collection<int, Feedback> */
    #[ORM\OneToMany(mappedBy: 'medecin', targetEntity: Feedback::class)]
    private Collection $feedbacks;

    public function __construct()
    {
        $this->rendezVous = new ArrayCollection();
        $this->feedbacks = new ArrayCollection();

        // Default role for doctors
        $this->roles = ['ROLE_MEDECIN'];
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getSpecialty(): ?string
    {
        return $this->specialty;
    }

    public function setSpecialty(string $specialty): self
    {
        $this->specialty = $specialty;

        return $this;
    }

    /**
     * Backwards-compatible alias used in some forms and SQL snippets.
     */
    public function getSpecialite(): ?string
    {
        return $this->specialty;
    }

    public function setSpecialite(string $specialite): self
    {
        $this->specialty = $specialite;

        return $this;
    }

    public function getCin(): string
    {
        return $this->cin;
    }

    public function setCin(string $cin): self
    {
        $this->cin = $cin;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getGovernorate(): ?string
    {
        return $this->governorate;
    }

    public function setGovernorate(?string $governorate): self
    {
        $this->governorate = $governorate;

        return $this;
    }

    public function getEducation(): ?string
    {
        return $this->education;
    }

    public function setEducation(?string $education): self
    {
        $this->education = $education;

        return $this;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function setExperience(?string $experience): self
    {
        $this->experience = $experience;

        return $this;
    }

    public function getIsVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function getAiAverageScore(): ?float
    {
        return $this->aiAverageScore;
    }

    public function setAiAverageScore(?float $aiAverageScore): self
    {
        $this->aiAverageScore = $aiAverageScore;

        return $this;
    }

    public function getAiScoreUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->aiScoreUpdatedAt;
    }

    public function setAiScoreUpdatedAt(?\DateTimeImmutable $aiScoreUpdatedAt): self
    {
        $this->aiScoreUpdatedAt = $aiScoreUpdatedAt;

        return $this;
    }

    /**
     * @return Collection<int, RendezVous>
     */
    public function getRendezVous(): Collection
    {
        return $this->rendezVous;
    }

    public function addRendezVou(RendezVous $rendezVous): self
    {
        if (!$this->rendezVous->contains($rendezVous)) {
            $this->rendezVous->add($rendezVous);
            $rendezVous->setDoctor($this);
        }

        return $this;
    }

    public function removeRendezVou(RendezVous $rendezVous): self
    {
        if ($this->rendezVous->removeElement($rendezVous)) {
            if ($rendezVous->getDoctor() === $this) {
                $rendezVous->setDoctor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Feedback>
     */
    public function getFeedbacks(): Collection
    {
        return $this->feedbacks;
    }

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
}