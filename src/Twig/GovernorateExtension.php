<?php

namespace App\Twig;

use App\Constants\Governorate;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class GovernorateExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('governorate_display', [$this, 'formatGovernorate']),
        ];
    }

    public function formatGovernorate(string $governorateValue): string
    {
        $choices = Governorate::getChoices();

        foreach ($choices as $displayName => $value) {
            if ($value === $governorateValue) {
                return $displayName;
            }
        }

        return $governorateValue;
    }
}
