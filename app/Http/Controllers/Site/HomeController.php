<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Display the home page.
     */
    public function __invoke(): Response
    {
        $posts = Post::query()
            ->published()
            ->orderByDesc('published_at')
            ->take(4)
            ->get()
            ->map(fn(Post $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'type' => $post->type->value,
                'category' => $post->category?->value,
                'excerpt' => $post->excerpt(),
                'published_at' => $post->published_at,
            ]);

        return Inertia::render('site/Home', [
            'posts' => $posts,
        ]);
    }
}
