<?php

declare(strict_types=1);

namespace Tests\Feature\Site;

use App\Models\Image;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_page_lists_all_projects_with_their_status(): void
    {
        Project::factory()->create(['title' => 'An Ongoing One', 'status' => 'ongoing']);
        Project::factory()->create(['title' => 'A Dormant One', 'status' => 'dormant']);

        $this->get(route('projects'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('site/Projects')
                ->has('projects', 2)
                ->where('projects.0.status', 'ongoing')
                ->where('projects.1.status', 'dormant'),
            );
    }

    public function test_project_show_page_renders_the_project(): void
    {
        $project = Project::factory()->create([
            'slug' => 'my-project',
            'blurb' => 'A short summary.',
            'body' => '**Hello**',
            'website_url' => 'https://example.com',
            'github_url' => 'https://github.com/me/project',
        ]);

        $this->get(route('projects.show', $project->slug))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->component('site/projects/Show')
                ->where('project.title', $project->title)
                ->where('project.body_html', "<p><strong>Hello</strong></p>\n")
                ->where('project.website_url', 'https://example.com')
                ->where('project.github_url', 'https://github.com/me/project'),
            );
    }

    public function test_project_show_page_includes_its_image_gallery_in_order(): void
    {
        $project = Project::factory()->create(['slug' => 'my-project']);
        $second = Image::factory()->create(['project_id' => $project->id, 'position' => 1]);
        $first = Image::factory()->create(['project_id' => $project->id, 'position' => 0]);

        $this->get(route('projects.show', $project->slug))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->has('project.images', 2)
                ->where('project.images.0.id', $first->id)
                ->where('project.images.1.id', $second->id),
            );
    }

    public function test_project_show_page_lists_only_published_posts_associated_with_it(): void
    {
        $project = Project::factory()->create(['slug' => 'my-project']);
        Post::factory()->published()->create(['project_id' => $project->id, 'title' => 'Published post']);
        Post::factory()->create(['project_id' => $project->id, 'title' => 'Draft post']);
        Post::factory()->published()->create(['title' => 'Unrelated post']);

        $this->get(route('projects.show', $project->slug))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                ->has('project.posts', 1)
                ->where('project.posts.0.title', 'Published post'),
            );
    }

    public function test_unknown_project_slug_returns_404(): void
    {
        $this->get(route('projects.show', 'does-not-exist'))
            ->assertNotFound();
    }
}
