<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'patients')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Patient extends BaseUser
{
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateOfBirth = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $hasInsurance = false;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $insuranceNumber = null;

    #[ORM\OneToMany(mappedBy: 'patient', targetEntity: Question::class)]
    private Collection $questions;

    public function __construct()
    {
        $this->roles = ['ROLE_PATIENT'];
        $this->questions = new ArrayCollection();
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

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    public function getHasInsurance(): bool
    {
        return $this->hasInsurance;
    }

    public function setHasInsurance(bool $hasInsurance): self
    {
        $this->hasInsurance = $hasInsurance;
        return $this;
    }

    public function getInsuranceNumber(): ?string
    {
        return $this->insuranceNumber;
    }

    public function setInsuranceNumber(?string $insuranceNumber): self
    {
        $this->insuranceNumber = $insuranceNumber;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = parent::getRoles();
        if (!in_array('ROLE_PATIENT', $roles)) {
            $roles[] = 'ROLE_PATIENT';
        }
        return array_unique($roles);
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setPatient($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getPatient() === $this) {
                $question->setPatient(null);
            }
        }

        return $this;
    }
}
