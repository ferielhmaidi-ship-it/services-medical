<?php

namespace App\Entity;

use App\Repository\MagazineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MagazineRepository::class)]
class Magazine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre du magazine est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le titre ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(
        min: 10,
        minMessage: "La description doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(
        message: "L’image doit être une URL valide."
    )]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date de création est obligatoire.")]
    #[Assert\Type(
        type: \DateTimeInterface::class,
        message: "La date de création doit être une date valide."
    )]
    private ?\DateTime $dateCreate = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le statut est obligatoire.")]
    #[Assert\Choice(
        choices: ['draft', 'published', 'archived'],
        message: "Le statut doit être : draft, published ou archived."
    )]
    private ?string $statut = null;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(mappedBy: 'magazine', targetEntity: Article::class, orphanRemoval: true)]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->dateCreate = new \DateTime(); // auto
    }

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getDateCreate(): ?\DateTime
    {
        return $this->dateCreate;
    }

    public function setDateCreate(\DateTime $dateCreate): static
    {
        $this->dateCreate = $dateCreate;
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

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setMagazine($this);
        }
        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            if ($article->getMagazine() === $this) {
                $article->setMagazine(null);
            }
        }
        return $this;
    }
}
