<?php

declare(strict_types=1);

namespace Tests\Feature\Site;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The sitemap is cached under a fixed key, which would otherwise
        // leak stale content between tests sharing the array cache driver.
        Cache::forget('sitemap.xml');
    }

    public function test_sitemap_is_served_as_xml(): void
    {
        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml');
    }

    public function test_sitemap_includes_static_pages(): void
    {
        $response = $this->get(route('sitemap'));

        foreach ([route('home'), route('about'), route('projects'), route('books'), route('blog.index'), route('recaps.index')] as $url) {
            $response->assertSee($url, false);
        }
    }

    public function test_sitemap_includes_published_posts_and_recaps_via_their_own_routes(): void
    {
        $post = Post::factory()->published()->create(['slug' => 'my-post']);
        $recap = Post::factory()->recap()->published()->create(['slug' => 'my-recap']);

        $this->get(route('sitemap'))
            ->assertSee(route('blog.show', $post->slug), false)
            ->assertSee(route('recaps.show', $recap->slug), false);
    }

    public function test_sitemap_excludes_unpublished_posts(): void
    {
        $draft = Post::factory()->create(['slug' => 'draft-post']);

        $this->get(route('sitemap'))
            ->assertDontSee(route('blog.show', $draft->slug), false);
    }
}
