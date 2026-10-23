<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Inertia\Inertia;
use Inertia\Response;

class RecapController extends Controller
{
    /**
     * Display published recaps.
     */
    public function index(): Response
    {
        $recaps = Post::query()
            ->recaps()
            ->published()
            ->orderByDesc('published_at')
            ->get(['id', 'title', 'slug', 'starts_at', 'ends_at', 'published_at']);

        return Inertia::render('site/recaps/Index', [
            'recaps' => $recaps,
        ]);
    }

    /**
     * Display a single published recap.
     */
    public function show(string $slug): Response
    {
        $recap = Post::query()->recaps()->published()->with('featuredImage')->where('slug', $slug)->firstOrFail();

        return Inertia::render('site/recaps/Show', [
            'recap' => [
                'title' => $recap->title,
                'slug' => $recap->slug,
                'excerpt' => $recap->excerpt(),
                'starts_at' => $recap->starts_at,
                'ends_at' => $recap->ends_at,
                'published_at' => $recap->published_at,
                'updated_at' => $recap->updated_at,
                'body_html' => $recap->toHtml(),
                'featured_image_url' => $recap->featuredImage?->url(1200),
            ],
        ]);
    }
}
