<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path' => 'images/'.fake()->sha256().'.jpg',
            'mime_type' => 'image/jpeg',
            'width' => fake()->numberBetween(400, 2400),
            'height' => fake()->numberBetween(400, 2400),
            'post_id' => null,
        ];
    }
}
