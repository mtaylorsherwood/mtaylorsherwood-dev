<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Seed a handful of demo blog posts and recaps.
     */
    public function run(): void
    {
        Post::factory()->published()->count(3)->create();
        Post::factory()->create();
        Post::factory()->scheduled()->create();

        Post::factory()->recap()->published()->count(3)->create();
        Post::factory()->recap()->create();
    }
}
