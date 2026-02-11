<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre ne peut pas etre vide.')]
    #[Assert\Length(
        min: 5,
        max: 30,
        minMessage: 'Le titre doit contenir au moins {{ limit }} caracteres.',
        maxMessage: 'Le titre ne peut pas depasser {{ limit }} caracteres.'
    )]
    private ?string $titre = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'La description ne peut pas etre vide.')]
    #[Assert\Length(
        min: 10,
        max: 1000,
        minMessage: 'La description doit contenir au moins {{ limit }} caracteres.',
        maxMessage: 'La description ne peut pas depasser {{ limit }} caracteres.'
    )]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Specialite::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Veuillez choisir une specialite.')]
    private ?Specialite $specialite = null;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Reponse::class, orphanRemoval: true)]
    private Collection $reponses;

    #[ORM\ManyToOne(targetEntity: Patient::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private ?int $likes = 0;

    #[ORM\ManyToMany(targetEntity: Patient::class)]
    #[ORM\JoinTable(name: 'question_likes_patients')]
    private Collection $likedByPatients;

    #[ORM\ManyToMany(targetEntity: Medecin::class)]
    #[ORM\JoinTable(name: 'question_likes_medecins')]
    private Collection $likedByMedecins;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->reponses = new ArrayCollection();
        $this->likedByPatients = new ArrayCollection();
        $this->likedByMedecins = new ArrayCollection();
        $this->likes = 0;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getSpecialite(): ?Specialite
    {
        return $this->specialite;
    }

    public function setSpecialite(?Specialite $specialite): self
    {
        $this->specialite = $specialite;
        return $this;
    }

    /**
     * @return Collection<int, Reponse>
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): self
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses->add($reponse);
            $reponse->setQuestion($this);
        }
        return $this;
    }

    public function removeReponse(Reponse $reponse): self
    {
        if ($this->reponses->removeElement($reponse)) {
            if ($reponse->getQuestion() === $this) {
                $reponse->setQuestion(null);
            }
        }
        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;
        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): self
    {
        $this->likes = $likes;
        return $this;
    }

    /**
     * @return Collection<int, BaseUser>
     */
    public function getLikedBy(): Collection
    {
        return $this->likedBy;
    }

    public function addLikedBy(BaseUser $user): self
    {
        if ($user instanceof Patient) {
            if (!$this->likedByPatients->contains($user)) {
                $this->likedByPatients->add($user);
            }
        } elseif ($user instanceof Medecin) {
             if (!$this->likedByMedecins->contains($user)) {
                $this->likedByMedecins->add($user);
            }
        }
        return $this;
    }

    public function removeLikedBy(BaseUser $user): self
    {
        if ($user instanceof Patient) {
            $this->likedByPatients->removeElement($user);
        } elseif ($user instanceof Medecin) {
            $this->likedByMedecins->removeElement($user);
        }
        return $this;
    }

    public function isLikedBy(BaseUser $user): bool
    {
        if ($user instanceof Patient) {
            return $this->likedByPatients->contains($user);
        } elseif ($user instanceof Medecin) {
            return $this->likedByMedecins->contains($user);
        }
        return false;
    }
}
