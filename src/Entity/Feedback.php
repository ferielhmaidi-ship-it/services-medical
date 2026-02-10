<?php

namespace App\Entity;

use App\Repository\FeedbackRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FeedbackRepository::class)]
class Feedback
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotNull(message: "La note est obligatoire")]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: "La note doit être entre 1 et 5")]
    #[ORM\Column]
    private ?int $rating = null;

    #[Assert\NotBlank(message: "Le commentaire est obligatoire")]
    #[Assert\Length(min: 10, max: 1000, minMessage: "Le commentaire doit contenir au moins 10 caractères")]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $comment = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne(targetEntity: Patient::class, inversedBy: 'feedbacks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne(targetEntity: Medecin::class, inversedBy: 'feedbacks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Medecin $medecin = null;

    #[ORM\ManyToOne(targetEntity: RendezVous::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?RendezVous $rendezVous = null;

    #[ORM\Column(nullable: true)]
    private ?float $sentimentScore = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // GETTERS & SETTERS
    public function getId(): ?int { return $this->id; }
    public function getRating(): ?int { return $this->rating; }
    public function setRating(int $rating): self { $this->rating = $rating; return $this; }
    public function getComment(): ?string { return $this->comment; }
    public function setComment(string $comment): self { $this->comment = $comment; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }
    public function getPatient(): ?Patient { return $this->patient; }
    public function setPatient(?Patient $patient): self { $this->patient = $patient; return $this; }
    public function getMedecin(): ?Medecin { return $this->medecin; }
    public function setMedecin(?Medecin $medecin): self { $this->medecin = $medecin; return $this; }
    public function getRendezVous(): ?RendezVous { return $this->rendezVous; }
    public function setRendezVous(?RendezVous $rendezVous): self { $this->rendezVous = $rendezVous; return $this; }

    public function getSentimentScore(): ?float { return $this->sentimentScore; }
    public function setSentimentScore(?float $sentimentScore): self { $this->sentimentScore = $sentimentScore; return $this; }

    public function getStars(): string { return str_repeat('⭐', $this->rating); }
}