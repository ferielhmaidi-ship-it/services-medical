<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'medecin')]
class Medecin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idmedecin', type: 'integer')]
    private ?int $idmedecin = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 100)]
    private ?string $nom = null;

    #[ORM\Column(name: 'specialite', type: 'string', length: 100)]
    private ?string $specialite = null;

    public function getIdmedecin(): ?int
    {
        return $this->idmedecin;
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

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): self
    {
        $this->specialite = $specialite;
        return $this;
    }
}
