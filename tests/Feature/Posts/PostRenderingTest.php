<?php

declare(strict_types=1);

namespace Tests\Feature\Posts;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PostRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_rendered_html_reflects_updates_rather_than_a_stale_cache(): void
    {
        $post = Post::factory()->create(['body' => 'Original body']);

        $this->assertStringContainsString('Original body', $post->toHtml());

        $post->update(['body' => 'Updated body']);

        $this->assertStringContainsString('Updated body', $post->toHtml());
        $this->assertStringNotContainsString('Original body', $post->toHtml());
    }

    public function test_the_rendered_html_cache_is_cleared_when_the_post_is_deleted(): void
    {
        $post = Post::factory()->create(['body' => 'Some body']);
        $post->toHtml();

        $post->delete();

        $this->assertFalse(Cache::has("posts:{$post->id}:html"));
    }

    public function test_raw_html_in_the_body_is_escaped_rather_than_executed(): void
    {
        $post = Post::factory()->create([
            'body' => 'Hello <script>alert(document.cookie)</script> world',
        ]);

        $html = $post->toHtml();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_an_image_onerror_payload_in_the_body_is_escaped(): void
    {
        $post = Post::factory()->create([
            'body' => '<img src="x" onerror="alert(1)">',
        ]);

        $html = $post->toHtml();

        $this->assertStringNotContainsString('<img src="x" onerror', $html);
        $this->assertStringContainsString('&lt;img', $html);
    }

    public function test_the_excerpt_decodes_html_entities_produced_by_markdown_rendering(): void
    {
        $post = Post::factory()->create([
            'body' => 'Adding a "Bookshelf" page, seen on other sites & liked the idea of it.',
        ]);

        $excerpt = $post->excerpt();

        $this->assertStringContainsString('"Bookshelf"', $excerpt);
        $this->assertStringContainsString('sites & liked', $excerpt);
        $this->assertStringNotContainsString('&quot;', $excerpt);
        $this->assertStringNotContainsString('&amp;', $excerpt);
    }
}
