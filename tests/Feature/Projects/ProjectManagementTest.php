<?php

declare(strict_types=1);

namespace Tests\Feature\Projects;

use App\Models\Image;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('dashboard.projects.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_all_projects(): void
    {
        $user = User::factory()->create();
        Project::factory()->count(3)->create();

        $this->actingAs($user)
            ->get(route('dashboard.projects.index'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('projects/Index')
                ->has('projects', 3),
            );
    }

    public function test_users_can_add_a_project(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.projects.store'), [
                'title' => 'My Cool Project',
                'status' => 'ongoing',
                'blurb' => 'A short summary of the project.',
                'body' => '## Overview',
                'website_url' => 'https://example.com',
                'github_url' => 'https://github.com/me/project',
            ])
            ->assertRedirect(route('dashboard.projects.index'));

        $this->assertDatabaseHas('projects', [
            'title' => 'My Cool Project',
            'slug' => 'my-cool-project',
            'status' => 'ongoing',
        ]);
    }

    public function test_project_creation_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.projects.store'), [])
            ->assertInvalid(['title', 'status', 'blurb', 'body']);
    }

    public function test_website_and_github_url_are_both_optional(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.projects.store'), [
                'title' => 'My Cool Project',
                'status' => 'ongoing',
                'blurb' => 'A short summary of the project.',
                'body' => '## Overview',
            ])
            ->assertRedirect(route('dashboard.projects.index'));

        $this->assertDatabaseHas('projects', [
            'title' => 'My Cool Project',
            'website_url' => null,
            'github_url' => null,
        ]);
    }

    public function test_duplicate_titles_get_a_unique_slug(): void
    {
        $user = User::factory()->create();
        Project::factory()->create(['title' => 'My Cool Project', 'slug' => 'my-cool-project']);

        $this->actingAs($user)->post(route('dashboard.projects.store'), [
            'title' => 'My Cool Project',
            'status' => 'ongoing',
            'blurb' => 'A short summary of the project.',
            'body' => '## Overview',
        ]);

        $this->assertDatabaseHas('projects', ['slug' => 'my-cool-project-2']);
    }

    public function test_users_can_attach_up_to_five_images_to_a_project(): void
    {
        $user = User::factory()->create();
        $images = Image::factory()->count(5)->create(['project_id' => null]);

        $this->actingAs($user)
            ->post(route('dashboard.projects.store'), [
                'title' => 'My Cool Project',
                'status' => 'ongoing',
                'blurb' => 'A short summary of the project.',
                'body' => '## Overview',
                'image_ids' => $images->pluck('id')->all(),
            ])
            ->assertRedirect(route('dashboard.projects.index'));

        $project = Project::query()->where('title', 'My Cool Project')->firstOrFail();
        $this->assertSame(5, $project->images()->count());
    }

    public function test_more_than_five_images_is_invalid(): void
    {
        $user = User::factory()->create();
        $images = Image::factory()->count(6)->create(['project_id' => null]);

        $this->actingAs($user)
            ->post(route('dashboard.projects.store'), [
                'title' => 'My Cool Project',
                'status' => 'ongoing',
                'blurb' => 'A short summary of the project.',
                'body' => '## Overview',
                'image_ids' => $images->pluck('id')->all(),
            ])
            ->assertInvalid(['image_ids']);
    }

    public function test_users_can_update_a_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['title' => 'Old Title']);

        $this->actingAs($user)
            ->put(route('dashboard.projects.update', $project), [
                'title' => 'New Title',
                'status' => $project->status->value,
                'blurb' => $project->blurb,
                'body' => $project->body,
            ])
            ->assertRedirect(route('dashboard.projects.index'));

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'title' => 'New Title']);
    }

    public function test_removing_an_image_from_the_list_detaches_it(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $image = Image::factory()->create(['project_id' => $project->id, 'position' => 0]);

        $this->actingAs($user)
            ->put(route('dashboard.projects.update', $project), [
                'title' => $project->title,
                'status' => $project->status->value,
                'blurb' => $project->blurb,
                'body' => $project->body,
                'image_ids' => [],
            ])
            ->assertRedirect(route('dashboard.projects.index'));

        $this->assertNull($image->refresh()->project_id);
    }

    public function test_users_can_delete_a_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $this->actingAs($user)
            ->delete(route('dashboard.projects.destroy', $project))
            ->assertRedirect(route('dashboard.projects.index'));

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
