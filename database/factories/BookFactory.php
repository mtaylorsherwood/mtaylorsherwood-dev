<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BookCategory;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Genres drawn from repeatedly, rather than free text, so seeded data
     * produces meaningful (repeating) filter options.
     *
     * @var array<int, string>
     */
    private const GENRES = [
        'Fantasy', 'Science Fiction', 'Biography', 'History',
        'Technology', 'Self-Help', 'Mystery', 'Memoir', 'Philosophy',
    ];

    /**
     * @var array<int, string>
     */
    private const SERIES = [
        'The Expanse', 'Mistborn', 'The Wheel of Time', 'Foundation',
    ];

    /**
     * @var array<int, string>
     */
    private const AUTHORS = [
        'Brandon Sanderson', 'N.K. Jemisin', 'James S.A. Corey', 'Isaac Asimov',
        'Yuval Noah Harari', 'Cal Newport', 'Michael Lewis', 'Tara Westover',
        'Robert Greene', 'Andy Weir',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-2 years', '-1 week');
        $finishedAt = fake()->dateTimeBetween($startedAt, 'now');

        return [
            'title' => rtrim(fake()->sentence(random_int(2, 5)), '.'),
            'author' => fake()->randomElement(self::AUTHORS),
            'category' => fake()->randomElement(BookCategory::cases()),
            'genre' => fake()->randomElement(self::GENRES),
            'pages' => fake()->numberBetween(120, 650),
            'series' => fake()->optional(0.3)->randomElement(self::SERIES),
            'review' => fake()->optional(0.5)->paragraph(),
            'started_at' => $startedAt,
            'finished_at' => $finishedAt,
        ];
    }

    /**
     * Indicate that the book is still being read.
     */
    public function currentlyReading(): static
    {
        return $this->state(fn(array $attributes) => [
            'finished_at' => null,
            'review' => null,
        ]);
    }

    /**
     * Indicate that the book has not been started yet.
     */
    public function toBeRead(): static
    {
        return $this->state(fn(array $attributes) => [
            'started_at' => null,
            'finished_at' => null,
            'review' => null,
        ]);
    }
}
