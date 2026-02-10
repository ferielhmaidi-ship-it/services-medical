<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'document')]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'iddocument', type: 'integer')]
    private ?int $iddocument = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 255)]
    private ?string $nom = null;

    #[ORM\Column(name: 'type', type: 'string', length: 50)]
    private ?string $type = null;

    #[ORM\Column(name: 'chemin', type: 'string', length: 255)]
    private ?string $chemin = null;

    #[ORM\Column(name: 'taille', type: 'integer')]
    private ?int $taille = null;

    #[ORM\Column(name: 'description', type: 'text')]
    private ?string $description = null;

    #[ORM\Column(name: 'createdAt', type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updatedAt', type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'idmedecin', referencedColumnName: 'idmedecin', nullable: false)]
    private ?Medecin $idmedecin = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'idpatient', referencedColumnName: 'idpatient', nullable: false)]
    private ?Patient $idpatient = null;

    // ===================== GETTERS & SETTERS =====================

    public function getIddocument(): ?int
    {
        return $this->iddocument;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getChemin(): ?string
    {
        return $this->chemin;
    }

    public function setChemin(string $chemin): self
    {
        $this->chemin = $chemin;
        return $this;
    }

    public function getTaille(): ?int
    {
        return $this->taille;
    }

    public function setTaille(int $taille): self
    {
        $this->taille = $taille;
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

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setCreatedAtFromString(string $date): self
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $date);
        if ($dateTime === false) {
            throw new \InvalidArgumentException(
                "Format de date invalide. Format attendu : YYYY-MM-DD HH:MM"
            );
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
            throw new \InvalidArgumentException(
                "Format de date invalide. Format attendu : YYYY-MM-DD HH:MM"
            );
        }
        $this->updatedAt = $dateTime;
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

    public function getIdpatient(): ?Patient
    {
        return $this->idpatient;
    }

    public function setIdpatient(?Patient $idpatient): self
    {
        $this->idpatient = $idpatient;
        return $this;
    }
}
