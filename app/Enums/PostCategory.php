<?php

declare(strict_types=1);

namespace App\Enums;

enum PostCategory: string
{
    case General = 'general';
    case Life = 'life';
    case Tech = 'tech';

    public function label(): string
    {
        return match ($this) {
            self::General => 'General',
            self::Life => 'Life',
            self::Tech => 'Tech',
        };
    }
}
