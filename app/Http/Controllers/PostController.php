<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Posts\PublishPostRequest;
use App\Http\Requests\Posts\StorePostRequest;
use App\Http\Requests\Posts\UpdatePostRequest;
use App\Models\Book;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * Display all posts (articles and recaps alike).
     */
    public function index(): Response
    {
        $posts = Post::query()->orderByDesc('created_at')->paginate(15);

        return Inertia::render('posts/Index', [
            'posts' => $posts,
        ]);
    }

    /**
     * Show the form for creating a post.
     */
    public function create(): Response
    {
        return Inertia::render('posts/Create', [
            'books' => $this->bookSummaries(),
            'projects' => $this->projectSummaries(),
        ]);
    }

    /**
     * Store a newly created post as a draft.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        /** @var string $title */
        $title = $request->validated('title');

        $post = Post::create([
            ...$request->validated(),
            'slug' => $this->uniqueSlug($title),
        ]);

        $post->reconcileImages();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Post saved as a draft.')]);

        return to_route('dashboard.posts.edit', $post);
    }

    /**
     * Show the form for editing a post.
     */
    public function edit(Post $post): Response
    {
        return Inertia::render('posts/Edit', [
            'post' => $this->postWithFeaturedImage($post),
            'books' => $this->bookSummaries(),
            'projects' => $this->projectSummaries(),
        ]);
    }

    /**
     * Update the given post.
     */
    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $post->update($request->validated());
        $post->reconcileImages();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Post updated.')]);

        return to_route('dashboard.posts.edit', $post);
    }

    /**
     * Delete the given post.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Post deleted.')]);

        return to_route('dashboard.posts.index');
    }

    /**
     * Publish the given post, either immediately or at a chosen (possibly future) datetime.
     */
    public function publish(PublishPostRequest $request, Post $post): RedirectResponse
    {
        /** @var string|null $publishedAtInput */
        $publishedAtInput = $request->validated('published_at');

        $post->publish($publishedAtInput ? Carbon::parse($publishedAtInput) : null);

        $publishedAt = $post->published_at ?? Carbon::now();

        $message = $publishedAt->isFuture()
            ? __('Post scheduled to publish :date.', ['date' => $publishedAt->format('M j, Y g:i A')])
            : __('Post published.');

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return to_route('dashboard.posts.edit', $post);
    }

    /**
     * Get a lightweight summary of every finished book, for client-side recap book-list generation.
     *
     * @return array<int, array<string, mixed>>
     */
    private function bookSummaries(): array
    {
        /** @var array<int, array<string, mixed>> $summaries */
        $summaries = Book::query()
            ->whereNotNull('finished_at')
            ->orderBy('finished_at')
            ->get(['id', 'title', 'author', 'category', 'genre', 'series', 'finished_at'])
            ->toArray();

        return $summaries;
    }

    /**
     * Get a lightweight summary of every project, for the post form's
     * optional project picker.
     *
     * @return array<int, array<string, mixed>>
     */
    private function projectSummaries(): array
    {
        /** @var array<int, array<string, mixed>> $summaries */
        $summaries = Project::query()->orderBy('title')->get(['id', 'title'])->toArray();

        return $summaries;
    }

    /**
     * Serialize a post for the edit form, including its featured image (if any).
     *
     * @return array<array-key, mixed>
     */
    private function postWithFeaturedImage(Post $post): array
    {
        $post->loadMissing('featuredImage');

        return [
            ...$post->toArray(),
            'featured_image' => $post->featuredImage !== null ? [
                'id' => $post->featuredImage->id,
                'url' => $post->featuredImage->url(400),
            ] : null,
        ];
    }

    /**
     * Generate a unique slug from the given title.
     */
    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 2;

        while (Post::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
