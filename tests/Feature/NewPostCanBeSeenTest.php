<?php

declare(strict_types=1);

use App\Models\Post;
use Tests\TestCase;

class NewPostCanBeSeenTest extends TestCase
{
    /**
     * A basic test for the app response.
     */
    public function test_a_new_post_can_be_seen_on_the_main_page(): void
    {
        $post = Post::factory(['published_at' => now()])->create();

        $response = $this->get('/');

        $response
            ->assertStatus(200)
            ->assertSee($post->title)
            ->assertSee($post->post_content);
    }
}
