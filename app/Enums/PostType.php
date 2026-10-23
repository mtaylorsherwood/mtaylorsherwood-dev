<?php

declare(strict_types=1);

namespace App\Enums;

enum PostType: string
{
    case Post = 'post';
    case Recap = 'recap';
    case GardenUpdate = 'garden_update';
    case ProjectUpdate = 'project_update';

    public function label(): string
    {
        return match ($this) {
            self::Post => 'Post',
            self::Recap => 'Reading Recap',
            self::GardenUpdate => 'Garden Update',
            self::ProjectUpdate => 'Project Update',
        };
    }

    /**
     * The default excerpt length (in characters) for this post type. Recaps
     * and project updates tend to open with more scene-setting before the
     * actual list of updates, so they get a longer budget than a regular
     * post before being truncated.
     */
    public function excerptLength(): int
    {
        return match ($this) {
            self::Post => 160,
            self::Recap, self::GardenUpdate, self::ProjectUpdate => 260,
        };
    }
}
