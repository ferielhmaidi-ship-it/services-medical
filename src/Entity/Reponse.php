<?php

namespace App\Entity;

use App\Repository\ReponseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReponseRepository::class)]
class Reponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'La reponse ne peut pas etre vide.')]
    #[Assert\Length(
        min: 5,
        max: 1000,
        minMessage: 'La reponse doit contenir au moins {{ limit }} caracteres.',
        maxMessage: 'La reponse ne peut pas depasser {{ limit }} caracteres.'
    )]
    private ?string $contenu = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: 'reponses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Veuillez choisir une question.')]
    private ?Question $question = null;

    #[ORM\ManyToOne(targetEntity: Medecin::class, inversedBy: 'reponses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Medecin $medecin = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private ?int $likes = 0;

    #[ORM\ManyToMany(targetEntity: Patient::class)]
    #[ORM\JoinTable(name: 'reponse_likes_patients')]
    private Collection $likedByPatients;

    #[ORM\ManyToMany(targetEntity: Medecin::class)]
    #[ORM\JoinTable(name: 'reponse_likes_medecins')]
    private Collection $likedByMedecins;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->likedByPatients = new ArrayCollection();
        $this->likedByMedecins = new ArrayCollection();
        $this->likes = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;
        return $this;
    }

    public function getMedecin(): ?Medecin
    {
        return $this->medecin;
    }

    public function setMedecin(?Medecin $medecin): self
    {
        $this->medecin = $medecin;
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
