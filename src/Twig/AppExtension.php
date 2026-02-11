<?php
// src/Twig/AppExtension.php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('age', [$this, 'calculateAge']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('calculate_age', [$this, 'calculateAge']),
        ];
    }

    public function calculateAge($birthdate): ?int
    {
        if (!$birthdate) {
            return null;
        }

        // If it's a string, convert to DateTime
        if (is_string($birthdate)) {
            $birthdate = new \DateTime($birthdate);
        }

        // If it's already a DateTime object
        if ($birthdate instanceof \DateTimeInterface) {
            $today = new \DateTime();
            $interval = $today->diff($birthdate);
            return $interval->y;
        }

        return null;
    }
}
