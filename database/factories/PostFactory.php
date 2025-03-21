<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'slug' => fn(array $attributes) => Str::slug($attributes['title']),
            'post_content' => $this->faker->paragraphs(3, true),
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 year'),
        ];
    }
}
