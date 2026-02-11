<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'medecins')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['cin'], message: 'This CIN is already registered')]
class Medecin extends BaseUser
{
    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Please select your medical specialty')]
    private string $specialty;

    #[ORM\Column(type: 'string', length: 8)]
    #[Assert\Length(
        min: 8,
        max: 8,
        exactMessage: 'CIN must be exactly 8 digits'
    )]
    #[Assert\Regex(
        pattern: '/^\d{8}$/',
        message: 'CIN must contain only digits'
    )]
    private string $cin;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $education = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $experience = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $governorate = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isVerified = false;

    public function __construct()
    {
        $this->roles = ['ROLE_MEDECIN'];
        $this->isVerified = false;
    }

    public function getSpecialty(): string
    {
        return $this->specialty;
    }

    public function setSpecialty(string $specialty): self
    {
        $this->specialty = $specialty;
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

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;
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

    public function getGovernorate(): ?string
    {
        return $this->governorate;
    }

    public function setGovernorate(?string $governorate): self
    {
        $this->governorate = $governorate;
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

    public function getRoles(): array
    {
        $roles = parent::getRoles();
        if (!in_array('ROLE_MEDECIN', $roles)) {
            $roles[] = 'ROLE_MEDECIN';
        }
        return array_unique($roles);
    }
}
