<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Post;
use Tests\TestCase;

class PostCanBePublishedTest extends TestCase
{
    public function test_post_can_be_published()
    {
        /** @var Post $post */
        $post = Post::factory(['published_at' => null])->create();

        $this->assertFalse($post->published());

        $post->publish();

        $this->assertTrue($post->published());
    }
}
