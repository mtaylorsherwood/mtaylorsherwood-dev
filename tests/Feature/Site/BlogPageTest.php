<?php

declare(strict_types=1);

namespace Tests\Feature\Site;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BlogPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_and_due_items_are_listed(): void
    {
        Post::factory()->published()->count(2)->create();
        Post::factory()->create(); // draft
        Post::factory()->scheduled()->create(); // future

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('site/blog/Index')
                ->has('posts.data', 2),
            );
    }

    public function test_recaps_are_listed_alongside_posts_on_the_blog_index(): void
    {
        Post::factory()->published()->create();
        Post::factory()->recap()->published()->create();

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('site/blog/Index')
                ->has('posts.data', 2),
            );
    }

    public function test_the_blog_index_is_paginated(): void
    {
        Post::factory()->published()->count(15)->create();

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('site/blog/Index')
                ->has('posts.data', 10)
                ->where('posts.current_page', 1)
                ->where('posts.last_page', 2),
            );

        $this->get(route('blog.index', ['page' => 2]))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->has('posts.data', 5)
                ->where('posts.current_page', 2),
            );
    }

    public function test_published_post_show_page_renders_html_body(): void
    {
        $post = Post::factory()->published()->create([
            'slug' => 'my-post',
            'body' => '**Hello**',
        ]);

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('site/blog/Show')
                ->where('post.body_html', "<p><strong>Hello</strong></p>\n"),
            );
    }

    public function test_draft_post_returns_404(): void
    {
        $post = Post::factory()->create(['slug' => 'draft-post']);

        $this->get(route('blog.show', $post->slug))
            ->assertNotFound();
    }

    public function test_future_scheduled_post_returns_404_until_due(): void
    {
        $post = Post::factory()->create([
            'slug' => 'future-post',
            'status' => 'published',
            'published_at' => Carbon::now()->addDay(),
        ]);

        $this->get(route('blog.show', $post->slug))
            ->assertNotFound();
    }

    public function test_unknown_post_slug_returns_404(): void
    {
        $this->get(route('blog.show', 'does-not-exist'))
            ->assertNotFound();
    }

    public function test_recap_slug_is_not_shown_via_the_blog_route(): void
    {
        $recap = Post::factory()->recap()->published()->create(['slug' => 'my-recap']);

        $this->get(route('blog.show', $recap->slug))
            ->assertNotFound();
    }

    public function test_garden_update_is_listed_and_reachable_via_the_blog_route(): void
    {
        $post = Post::factory()->gardenUpdate()->published()->create(['slug' => 'my-garden-update']);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('site/blog/Index')
                ->has('posts.data', 1),
            );

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->component('site/blog/Show'));
    }
}
