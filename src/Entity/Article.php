<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre de l’article est obligatoire.")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le titre ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le résumé est obligatoire.")]
    #[Assert\Length(
        min: 20,
        minMessage: "Le résumé doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $resume = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de l’auteur est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le nom de l’auteur doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $auteur = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date de publication est obligatoire.")]
    #[Assert\Type(
        type: \DateTimeInterface::class,
        message: "La date de publication doit être une date valide."
    )]
    private ?\DateTimeInterface $datePub = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\NotNull(message: "Le magazine est obligatoire.")]
    private ?Magazine $magazine = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le statut est obligatoire.")]
    #[Assert\Choice(
        choices: ['draft', 'published'],
        message: "Le statut doit être : draft ou published."
    )]
    private ?string $statut = null;

    // ---------------- GETTERS & SETTERS ----------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function setResume(string $resume): static
    {
        $this->resume = $resume;
        return $this;
    }

    public function getAuteur(): ?string
    {
        return $this->auteur;
    }

    public function setAuteur(string $auteur): static
    {
        $this->auteur = $auteur;
        return $this;
    }

    public function getDatePub(): ?\DateTimeInterface
    {
        return $this->datePub;
    }

    public function setDatePub(\DateTimeInterface $datePub): static
    {
        $this->datePub = $datePub;
        return $this;
    }

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }

    public function setMagazine(?Magazine $magazine): static
    {
        $this->magazine = $magazine;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }
}
