<?php

declare(strict_types=1);

namespace Tests\Feature\Site;

use Tests\TestCase;

class RobotsTest extends TestCase
{
    public function test_robots_txt_disallows_authenticated_routes_and_links_the_sitemap(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /dashboard', false)
            ->assertSee('Disallow: /settings', false)
            ->assertSee('Sitemap: '.route('sitemap'), false);
    }
}
