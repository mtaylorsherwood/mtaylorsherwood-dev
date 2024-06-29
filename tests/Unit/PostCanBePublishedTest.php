<?php

declare(strict_types=1);

use App\Models\Post;
use Tests\TestCase;

class PostCanBePublishedTest extends TestCase
{
    public function test_post_can_be_published()
    {
        $post = Post::factory(['published_at' => null])->create();

        $this->assertFalse($post->published());

        $post->publish();

        $this->assertTrue($post->published());
    }
}
