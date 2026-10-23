<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * Display published blog posts and recaps together, newest first.
     */
    public function index(): Response
    {
        $posts = Post::query()
            ->published()
            ->with('featuredImage')
            ->orderByDesc('published_at')
            ->paginate(10)
            ->through(fn(Post $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'type' => $post->type->value,
                'category' => $post->category?->value,
                'excerpt' => $post->excerpt(),
                'published_at' => $post->published_at,
                'featured_image_url' => $post->featuredImage?->url(800),
            ]);

        return Inertia::render('site/blog/Index', [
            'posts' => $posts,
        ]);
    }

    /**
     * Display a single published blog post.
     */
    public function show(string $slug): Response
    {
        $post = Post::query()->posts()->published()->with('featuredImage')->where('slug', $slug)->firstOrFail();

        return Inertia::render('site/blog/Show', [
            'post' => [
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt(),
                'published_at' => $post->published_at,
                'updated_at' => $post->updated_at,
                'body_html' => $post->toHtml(),
                'featured_image_url' => $post->featuredImage?->url(1200),
            ],
        ]);
    }
}
