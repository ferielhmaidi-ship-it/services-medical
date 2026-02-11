<?php

namespace App\Security;

use App\Entity\Admin;
use App\Entity\Medecin;
use App\Entity\Patient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AppUserProvider implements UserProviderInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->entityManager->getRepository(Admin::class)->findOneBy(['email' => $identifier]);

        if (!$user) {
            $user = $this->entityManager->getRepository(Medecin::class)->findOneBy(['email' => $identifier]);
        }

        if (!$user) {
            $user = $this->entityManager->getRepository(Patient::class)->findOneBy(['email' => $identifier]);
        }

        if (!$user) {
            throw new UserNotFoundException(sprintf('User with email "%s" not found.', $identifier));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof Admin && !$user instanceof Medecin && !$user instanceof Patient) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return Admin::class === $class || Medecin::class === $class || Patient::class === $class;
    }
}
