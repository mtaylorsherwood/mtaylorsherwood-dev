<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SsrExclusionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The suite otherwise forces SSR off entirely (see phpunit.xml); turn
        // it back on here so these tests can prove the per-route exclusion
        // works, without requiring a built SSR bundle to exist.
        config([
            'inertia.ssr.enabled' => true,
            'inertia.ssr.ensure_bundle_exists' => false,
        ]);
    }

    public function test_dashboard_skips_ssr(): void
    {
        Http::fake();

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk();

        Http::assertNothingSent();
    }

    public function test_login_skips_ssr(): void
    {
        Http::fake();

        $this->get(route('login'))->assertOk();

        Http::assertNothingSent();
    }

    public function test_settings_skips_ssr(): void
    {
        Http::fake();

        $this->actingAs(User::factory()->create())
            ->get(route('profile.edit'))
            ->assertOk();

        Http::assertNothingSent();
    }

    public function test_public_site_pages_still_use_ssr(): void
    {
        Http::fake([
            '*' => Http::response(['head' => [], 'body' => '<div id="app"></div>']),
        ]);

        $this->get(route('about'))->assertOk();

        // Hits either the production `/render` endpoint or the Vite dev
        // server's `/__inertia_ssr`, depending on whether `public/hot` is
        // present in the environment running the suite.
        Http::assertSentCount(1);
    }
}
