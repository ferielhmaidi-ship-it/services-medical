<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'rendezvous')]
class Rendezvous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idrendezvous', type: 'integer')]
    private ?int $idrendezvous = null;

    #[ORM\Column(name: 'specialite', type: 'string', length: 100)]
    private ?string $specialite = null;

    #[ORM\Column(name: 'date', type: 'datetime')]
    private ?\DateTimeInterface $date = null;

    public function getIdrendezvous(): ?int
    {
        return $this->idrendezvous;
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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }
}
