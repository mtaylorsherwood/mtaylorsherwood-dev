<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BookSeeder extends Seeder
{
    /**
     * Seed a personal reading history from 2021 to now.
     */
    public function run(): void
    {
        /** @var Carbon $cursor */
        $cursor = Carbon::create(2021, 1, 1);
        $now = Carbon::now();

        while ($cursor->lessThan($now->clone()->subMonth())) {
            $booksThisMonth = fake()->numberBetween(0, 3);

            for ($i = 0; $i < $booksThisMonth; $i++) {
                $startedAt = $cursor->clone()->addDays(fake()->numberBetween(0, 20));
                $finishedAt = $startedAt->clone()->addDays(fake()->numberBetween(3, 21))->min($now);

                Book::factory()->create([
                    'started_at' => $startedAt,
                    'finished_at' => $finishedAt,
                ]);
            }

            $cursor->addMonth();
        }

        foreach (range(1, 2) as $i) {
            Book::factory()->currentlyReading()->create([
                'started_at' => $now->clone()->subDays(fake()->numberBetween(3, 21)),
            ]);
        }

        Book::factory()->toBeRead()->count(3)->create();
    }
}
