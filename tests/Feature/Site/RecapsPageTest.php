<?php

declare(strict_types=1);

namespace Tests\Feature\Site;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RecapsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_recaps_are_listed(): void
    {
        Post::factory()->recap()->published()->count(2)->create();
        Post::factory()->recap()->create();

        $this->get(route('recaps.index'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('site/recaps/Index')
                ->has('recaps', 2),
            );
    }

    public function test_future_scheduled_recaps_are_not_listed(): void
    {
        Post::factory()->recap()->scheduled()->create();

        $this->get(route('recaps.index'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('site/recaps/Index')
                ->has('recaps', 0),
            );
    }

    public function test_published_recap_show_page_renders_html_body(): void
    {
        $recap = Post::factory()->recap()->published()->create([
            'slug' => 'my-recap',
            'body' => '**Hello**',
        ]);

        $this->get(route('recaps.show', $recap->slug))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('site/recaps/Show')
                ->where('recap.body_html', "<p><strong>Hello</strong></p>\n"),
            );
    }

    public function test_draft_recap_returns_404(): void
    {
        $recap = Post::factory()->recap()->create(['slug' => 'draft-recap']);

        $this->get(route('recaps.show', $recap->slug))
            ->assertNotFound();
    }

    public function test_future_scheduled_recap_returns_404_until_due(): void
    {
        $recap = Post::factory()->recap()->create([
            'slug' => 'future-recap',
            'status' => 'published',
            'published_at' => Carbon::now()->addDay(),
        ]);

        $this->get(route('recaps.show', $recap->slug))
            ->assertNotFound();
    }

    public function test_unknown_recap_slug_returns_404(): void
    {
        $this->get(route('recaps.show', 'does-not-exist'))
            ->assertNotFound();
    }

    public function test_article_slug_is_not_shown_via_the_recaps_route(): void
    {
        $article = Post::factory()->published()->create(['slug' => 'my-article']);

        $this->get(route('recaps.show', $article->slug))
            ->assertNotFound();
    }
}
