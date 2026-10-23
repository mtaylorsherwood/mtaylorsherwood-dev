<?php

declare(strict_types=1);

namespace App\Enums;

enum BookCategory: string
{
    case Fiction = 'fiction';
    case NonFiction = 'non_fiction';

    public function label(): string
    {
        return match ($this) {
            self::Fiction => 'Fiction',
            self::NonFiction => 'Non-Fiction',
        };
    }
}
