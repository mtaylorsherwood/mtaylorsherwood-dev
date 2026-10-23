<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state. Produces a draft article.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->sentence(6), '.');

        /** @var array<int, string> $paragraphs */
        $paragraphs = fake()->paragraphs(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 100000),
            'type' => PostType::Post,
            'body' => "## Introduction\n\n".implode("\n\n", $paragraphs),
            'status' => PostStatus::Draft,
            'published_at' => null,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }

    /**
     * Indicate that the post is a reading recap covering a date range.
     */
    public function recap(): static
    {
        return $this->state(function () {
            $startsAt = fake()->dateTimeBetween('-2 years', '-1 month');
            $endsAt = (clone $startsAt)->modify('+1 month -1 day');
            $title = rtrim(fake()->sentence(3), '.').' Reading Recap';

            /** @var array<int, string> $paragraphs */
            $paragraphs = fake()->paragraphs(3);

            return [
                'title' => $title,
                'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 100000),
                'type' => PostType::Recap,
                'body' => "## What I read\n\n".implode("\n\n", $paragraphs),
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ];
        });
    }

    /**
     * Indicate that the post is a garden update.
     */
    public function gardenUpdate(): static
    {
        return $this->state(fn(): array => [
            'type' => PostType::GardenUpdate,
        ]);
    }

    /**
     * Indicate that the post is a project update, linked to a project.
     */
    public function projectUpdate(): static
    {
        return $this->state(fn(): array => [
            'type' => PostType::ProjectUpdate,
        ]);
    }

    /**
     * Indicate that the post is published and already visible.
     */
    public function published(): static
    {
        return $this->state(fn(): array => [
            'status' => PostStatus::Published,
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the post is published with a future date, so it stays
     * hidden on the public site until that date passes.
     */
    public function scheduled(): static
    {
        return $this->state(fn(): array => [
            'status' => PostStatus::Published,
            'published_at' => fake()->dateTimeBetween('+1 day', '+2 weeks'),
        ]);
    }
}
