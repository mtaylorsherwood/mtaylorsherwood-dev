<?php

declare(strict_types=1);

namespace App\Enums;

enum ProjectStatus: string
{
    case Ongoing = 'ongoing';
    case Dormant = 'dormant';

    public function label(): string
    {
        return match ($this) {
            self::Ongoing => 'Ongoing',
            self::Dormant => 'Dormant',
        };
    }
}
