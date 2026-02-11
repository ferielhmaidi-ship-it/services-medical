<?php

namespace App\Security;

use App\Entity\Admin;
use App\Entity\Medecin;
use App\Entity\Patient;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof Admin && !$user instanceof Medecin && !$user instanceof Patient) {
            return;
        }

        // Check if user is active
        if (!$user->getIsActive()) {
            throw new CustomUserMessageAuthenticationException('Votre compte est inactif. Veuillez contacter un administrateur.');
        }

        // For Medecins, check if they are verified
        //if ($user instanceof Medecin && !$user->getIsVerified()) {
          //  throw new CustomUserMessageAuthenticationException('Your account is not yet verified. Please wait for administrator verification.');
        //}
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // No checks needed after authentication
    }
}
