<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Martyn Taylor-Sherwood',
            'email' => 'martyn@taylorsherwood.co.uk',
        ]);

        Post::factory()->count(rand(3, 25))->create();
    }
}
