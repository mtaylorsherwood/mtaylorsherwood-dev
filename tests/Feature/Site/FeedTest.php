<?php

declare(strict_types=1);

namespace Tests\Feature\Site;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_is_served_as_rss_xml(): void
    {
        $this->get(route('feed'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/rss+xml');
    }

    public function test_feed_includes_published_posts_and_recaps_via_their_own_routes(): void
    {
        $post = Post::factory()->published()->create(['slug' => 'my-post', 'title' => 'My Post']);
        $recap = Post::factory()->recap()->published()->create(['slug' => 'my-recap', 'title' => 'My Recap']);

        $this->get(route('feed'))
            ->assertSee('My Post')
            ->assertSee(route('blog.show', $post->slug), false)
            ->assertSee('My Recap')
            ->assertSee(route('recaps.show', $recap->slug), false);
    }

    public function test_feed_excludes_unpublished_and_future_scheduled_posts(): void
    {
        $draft = Post::factory()->create(['slug' => 'draft-post']);
        $scheduled = Post::factory()->scheduled()->create(['slug' => 'future-post']);

        $this->get(route('feed'))
            ->assertDontSee(route('blog.show', $draft->slug), false)
            ->assertDontSee(route('blog.show', $scheduled->slug), false);
    }

    public function test_feed_is_capped_at_twenty_items(): void
    {
        Post::factory()->published()->count(25)->create();

        $response = $this->get(route('feed'))->assertOk();

        $this->assertSame(20, substr_count($response->getContent(), '<item>'));
    }
}
