<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Book;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_posts_metric_only_counts_published_posts_and_compares_the_same_partial_year(): void
    {
        Carbon::setTestNow('2026-08-19 12:00:00');
        $user = User::factory()->create();

        // This year, within the Jan 1 - Aug 19 window: counts.
        Post::factory()->count(2)->create(['status' => PostStatus::Published, 'published_at' => '2026-03-01']);
        // Last year, within the same Jan 1 - Aug 19 window: counts as "previous".
        Post::factory()->create(['status' => PostStatus::Published, 'published_at' => '2025-05-15']);
        // Last year, but after Aug 19 - counts toward the total, but outside the
        // comparable window so must not count toward the "previous" comparison.
        Post::factory()->create(['status' => PostStatus::Published, 'published_at' => '2025-11-01']);
        // A draft: must not count anywhere, even though it has a type.
        Post::factory()->create(['status' => PostStatus::Draft, 'published_at' => null]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('Dashboard')
                ->where('metrics.posts.total', 4)
                ->where('metrics.posts.comparisons.0.current', 2)
                ->where('metrics.posts.comparisons.0.previous', 1)
                ->where('metrics.posts.comparisons.0.range', 'Jan 1 – Aug 19'),
            );
    }

    public function test_books_read_metric_compares_the_same_partial_month_and_partial_year(): void
    {
        Carbon::setTestNow('2026-08-19 12:00:00');
        $user = User::factory()->create();

        // This month (Aug 1 - 19, 2026): counts toward both this month and this year.
        Book::factory()->count(2)->create(['finished_at' => '2026-08-10']);
        // This year, but before this month: counts toward this year only.
        Book::factory()->create(['finished_at' => '2026-03-01']);
        // Same month last year (Aug 1 - 19, 2025): counts as "previous" for the month comparison
        // and also falls inside last year's Jan 1 - Aug 19 window for the year comparison.
        Book::factory()->create(['finished_at' => '2025-08-05']);
        // Last year, within Jan 1 - Aug 19, but not in August: counts toward the year comparison only.
        Book::factory()->create(['finished_at' => '2025-04-01']);
        // Last year, after Aug 19: counts toward the total, but outside both
        // comparable windows.
        Book::factory()->create(['finished_at' => '2025-12-01']);
        // Currently reading and to-be-read books must never count as "read".
        Book::factory()->currentlyReading()->create();
        Book::factory()->toBeRead()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('Dashboard')
                ->where('metrics.books_read.total', 6)
                ->where('metrics.books_read.comparisons.0.label', 'This month vs last year')
                ->where('metrics.books_read.comparisons.0.current', 2)
                ->where('metrics.books_read.comparisons.0.previous', 1)
                ->where('metrics.books_read.comparisons.1.label', 'This year vs last year')
                ->where('metrics.books_read.comparisons.1.current', 3)
                ->where('metrics.books_read.comparisons.1.previous', 2),
            );
    }

    public function test_books_tbr_metric_counts_only_unstarted_books(): void
    {
        $user = User::factory()->create();

        Book::factory()->count(2)->toBeRead()->create();
        Book::factory()->currentlyReading()->create();
        Book::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('Dashboard')
                ->where('metrics.books_tbr.total', 2)
                ->where('metrics.books_tbr.comparisons', []),
            );
    }
}
