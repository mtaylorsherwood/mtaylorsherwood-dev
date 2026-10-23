<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->sentence(3), '.');

        /** @var array<int, string> $paragraphs */
        $paragraphs = fake()->paragraphs(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 100000),
            'status' => fake()->randomElement(ProjectStatus::cases()),
            'blurb' => fake()->sentence(15),
            'body' => "## Overview\n\n".implode("\n\n", $paragraphs),
            'website_url' => fake()->optional()->url(),
            'github_url' => fake()->optional()->url(),
        ];
    }
}
