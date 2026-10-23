<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Display all projects.
     */
    public function index(): Response
    {
        $projects = Project::query()
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'slug', 'status', 'blurb', 'website_url', 'github_url']);

        return Inertia::render('site/Projects', [
            'projects' => $projects,
        ]);
    }

    /**
     * Display a single project, its images, and any associated posts.
     */
    public function show(string $slug): Response
    {
        $project = Project::query()->where('slug', $slug)->firstOrFail();

        return Inertia::render('site/projects/Show', [
            'project' => [
                'title' => $project->title,
                'slug' => $project->slug,
                'blurb' => $project->blurb,
                'body_html' => $project->toHtml(),
                'website_url' => $project->website_url,
                'github_url' => $project->github_url,
                'updated_at' => $project->updated_at,
                'images' => $project->images()->get()->map(fn($image) => [
                    'id' => $image->id,
                    'url' => $image->url(1200),
                ]),
                'posts' => $project->posts()
                    ->published()
                    ->get()
                    ->map(fn($post) => [
                        'id' => $post->id,
                        'title' => $post->title,
                        'slug' => $post->slug,
                        'type' => $post->type->value,
                        'excerpt' => $post->excerpt(),
                        'published_at' => $post->published_at,
                    ]),
            ],
        ]);
    }
}
