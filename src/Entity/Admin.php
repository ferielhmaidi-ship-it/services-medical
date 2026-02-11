<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\AdminRepository;

#[ORM\Entity]
#[ORM\Table(name: 'admins')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Admin extends BaseUser
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    public function __construct()
    {
        $this->roles = ['ROLE_ADMIN'];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = parent::getRoles();
        // Add ROLE_ADMIN for all admins
        if (!in_array('ROLE_ADMIN', $roles)) {
            $roles[] = 'ROLE_ADMIN';
        }
        return array_unique($roles);
    }
}
