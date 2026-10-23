<?php

declare(strict_types=1);

namespace Tests\Feature\Posts;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PostManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('dashboard.posts.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_all_posts_of_both_types(): void
    {
        $user = User::factory()->create();
        Post::factory()->create();
        Post::factory()->recap()->create();

        $this->actingAs($user)
            ->get(route('dashboard.posts.index'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('posts/Index')
                ->has('posts.data', 2),
            );
    }

    public function test_the_dashboard_posts_index_is_paginated(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(16)->create();

        $this->actingAs($user)
            ->get(route('dashboard.posts.index'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('posts/Index')
                ->has('posts.data', 15)
                ->where('posts.last_page', 2),
            );
    }

    public function test_users_can_create_an_article_which_is_saved_as_a_draft(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('dashboard.posts.store'), [
            'title' => 'How I Read More Books',
            'type' => PostType::Post->value,
            'body' => '## Some content',
        ]);

        $post = Post::query()->where('title', 'How I Read More Books')->firstOrFail();

        $response->assertRedirect(route('dashboard.posts.edit', $post));

        $this->assertSame('how-i-read-more-books', $post->slug);
        $this->assertSame(PostType::Post, $post->type);
        $this->assertSame(PostStatus::Draft, $post->status);
        $this->assertNull($post->published_at);
    }

    public function test_users_can_create_a_recap_with_a_date_range(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('dashboard.posts.store'), [
            'title' => 'August 2026 Reading Recap',
            'type' => PostType::Recap->value,
            'starts_at' => '2026-08-01',
            'ends_at' => '2026-08-31',
            'body' => '## Books I read',
        ]);

        $post = Post::query()->where('title', 'August 2026 Reading Recap')->firstOrFail();

        $this->assertSame(PostType::Recap, $post->type);
        $this->assertSame('2026-08-01', $post->starts_at?->toDateString());
        $this->assertSame('2026-08-31', $post->ends_at?->toDateString());
    }

    public function test_duplicate_titles_get_a_unique_slug(): void
    {
        $user = User::factory()->create();
        Post::factory()->create(['title' => 'How I Read More Books', 'slug' => 'how-i-read-more-books']);

        $this->actingAs($user)->post(route('dashboard.posts.store'), [
            'title' => 'How I Read More Books',
            'type' => PostType::Post->value,
            'body' => '## Some content',
        ]);

        $this->assertDatabaseHas('posts', ['slug' => 'how-i-read-more-books-2']);
    }

    public function test_a_post_can_be_associated_with_a_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $this->actingAs($user)->post(route('dashboard.posts.store'), [
            'title' => 'A Post About The Project',
            'type' => PostType::Post->value,
            'body' => 'Body',
            'project_id' => $project->id,
        ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'A Post About The Project',
            'project_id' => $project->id,
        ]);
    }

    public function test_project_id_must_reference_an_existing_project(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.posts.store'), [
                'title' => 'A Post',
                'type' => PostType::Post->value,
                'body' => 'Body',
                'project_id' => 99999,
            ])
            ->assertInvalid(['project_id']);
    }

    public function test_users_can_create_a_project_update_linked_to_a_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $this->actingAs($user)->post(route('dashboard.posts.store'), [
            'title' => 'Shipped a New Feature',
            'type' => PostType::ProjectUpdate->value,
            'body' => 'Body',
            'project_id' => $project->id,
        ]);

        $post = Post::query()->where('title', 'Shipped a New Feature')->firstOrFail();

        $this->assertSame(PostType::ProjectUpdate, $post->type);
        $this->assertSame($project->id, $post->project_id);
    }

    public function test_project_update_creation_requires_a_project(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.posts.store'), [
                'title' => 'Shipped a New Feature',
                'type' => PostType::ProjectUpdate->value,
                'body' => 'Body',
            ])
            ->assertInvalid(['project_id']);
    }

    public function test_category_is_rejected_for_non_post_types(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.posts.store'), [
                'title' => 'A Recap',
                'type' => PostType::Recap->value,
                'category' => 'general',
                'starts_at' => '2026-08-01',
                'ends_at' => '2026-08-31',
                'body' => 'Body',
            ])
            ->assertInvalid(['category']);
    }

    public function test_post_creation_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.posts.store'), [])
            ->assertInvalid(['title', 'type', 'body']);
    }

    public function test_recap_creation_requires_a_date_range(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.posts.store'), [
                'title' => 'A Recap',
                'type' => PostType::Recap->value,
                'body' => 'Body',
            ])
            ->assertInvalid(['starts_at', 'ends_at']);
    }

    public function test_article_creation_does_not_require_a_date_range(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.posts.store'), [
                'title' => 'An Article',
                'type' => PostType::Post->value,
                'body' => 'Body',
            ])
            ->assertValid(['starts_at', 'ends_at']);
    }

    public function test_recap_ends_at_must_not_be_before_starts_at(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.posts.store'), [
                'title' => 'A Recap',
                'type' => PostType::Recap->value,
                'starts_at' => '2026-08-15',
                'ends_at' => '2026-08-01',
                'body' => 'Body',
            ])
            ->assertInvalid(['ends_at']);
    }

    public function test_users_can_update_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['title' => 'Old Title']);

        $this->actingAs($user)
            ->put(route('dashboard.posts.update', $post), [
                'title' => 'New Title',
                'type' => $post->type->value,
                'body' => 'Updated body',
            ])
            ->assertRedirect(route('dashboard.posts.edit', $post));

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'New Title', 'body' => 'Updated body']);
    }

    public function test_updating_a_post_does_not_change_its_slug(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['slug' => 'original-slug']);

        $this->actingAs($user)->put(route('dashboard.posts.update', $post), [
            'title' => 'A Completely Different Title',
            'type' => $post->type->value,
            'body' => $post->body,
        ]);

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'slug' => 'original-slug']);
    }

    public function test_users_can_delete_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->delete(route('dashboard.posts.destroy', $post))
            ->assertRedirect(route('dashboard.posts.index'));

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_publishing_without_a_date_publishes_immediately(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.posts.publish', $post))
            ->assertRedirect(route('dashboard.posts.edit', $post));

        $post->refresh();

        $this->assertSame(PostStatus::Published, $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertTrue($post->published_at->lessThanOrEqualTo(Carbon::now()));
    }

    public function test_publishing_with_a_future_date_schedules_it(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $futureDate = Carbon::now()->addWeek()->startOfMinute();

        $this->actingAs($user)
            ->post(route('dashboard.posts.publish', $post), [
                'published_at' => $futureDate->toDateTimeString(),
            ])
            ->assertRedirect(route('dashboard.posts.edit', $post));

        $post->refresh();

        $this->assertSame(PostStatus::Published, $post->status);
        $this->assertTrue($post->published_at->equalTo($futureDate));
        $this->assertTrue($post->published_at->isFuture());

        // A future-dated publish should not yet be visible on the public site.
        $this->assertSame(0, Post::query()->published()->whereKey($post->id)->count());
    }
}
