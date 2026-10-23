<?php

declare(strict_types=1);

namespace Tests\Feature\Site;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SitePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_displayed_with_latest_published_posts()
    {
        Post::factory()->published()->count(3)->create();
        Post::factory()->create(); // draft, should not appear

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('site/Home')
                ->has('posts', 3),
            );
    }

    public function test_about_page_is_displayed()
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->component('site/About'));
    }

    public function test_projects_page_is_displayed()
    {
        $this->get(route('projects'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->component('site/Projects'));
    }

}
