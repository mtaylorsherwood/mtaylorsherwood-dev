<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Display all projects.
     */
    public function index(): Response
    {
        $projects = Project::query()->orderByDesc('created_at')->get();

        return Inertia::render('projects/Index', [
            'projects' => $projects,
        ]);
    }

    /**
     * Show the form for adding a project.
     */
    public function create(): Response
    {
        return Inertia::render('projects/Create');
    }

    /**
     * Store a newly created project.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $data = $request->validated();
        /** @var string $title */
        $title = $data['title'];
        /** @var array<int, int> $imageIds */
        $imageIds = $data['image_ids'] ?? [];
        unset($data['image_ids']);

        $project = Project::create([
            ...$data,
            'slug' => $this->uniqueSlug($title),
        ]);

        $project->syncImages($imageIds);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Project added.')]);

        return to_route('dashboard.projects.index');
    }

    /**
     * Show the form for editing a project.
     */
    public function edit(Project $project): Response
    {
        return Inertia::render('projects/Edit', [
            'project' => [
                ...$project->toArray(),
                'images' => $project->images()->get(['id', 'width', 'height'])->map(fn($image) => [
                    'id' => $image->id,
                    'url' => $image->url(400),
                ]),
            ],
        ]);
    }

    /**
     * Update the given project.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $data = $request->validated();
        /** @var array<int, int> $imageIds */
        $imageIds = $data['image_ids'] ?? [];
        unset($data['image_ids']);

        $project->update($data);
        $project->syncImages($imageIds);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Project updated.')]);

        return to_route('dashboard.projects.index');
    }

    /**
     * Delete the given project.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Project deleted.')]);

        return to_route('dashboard.projects.index');
    }

    /**
     * Generate a unique slug from the given title.
     */
    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 2;

        while (Project::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
