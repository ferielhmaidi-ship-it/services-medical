<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'patient')]
class Patient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idpatient', type: 'integer')]
    private ?int $idpatient = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 100)]
    private ?string $nom = null;

    #[ORM\Column(name: 'prenom', type: 'string', length: 100)]
    private ?string $prenom = null;

    public function getIdpatient(): ?int
    {
        return $this->idpatient;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }
}
