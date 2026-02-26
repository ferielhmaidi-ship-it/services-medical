<?php

namespace App\Twig;

use App\Constants\Specialty;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SpecialtyExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('specialty_display', [$this, 'formatSpecialty']),
        ];
    }

    public function formatSpecialty(?string $specialtyValue): string
    {
        if (!$specialtyValue) {
            return 'Not specified';
        }

        // Since we store the constant value (like 'Cardiologie'),
        // but want to display the French name, we need to get the key
        $choices = Specialty::getChoices();

        // The choices array is: display_name => constant_value
        // We need to find the display name for the constant value
        foreach ($choices as $displayName => $constantValue) {
            if ($constantValue === $specialtyValue) {
                return $displayName;
            }
        }

        // If not found, return the value as is
        return $specialtyValue;
    }
}
