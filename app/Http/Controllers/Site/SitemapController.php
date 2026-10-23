<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Generate the public sitemap: the static pages plus one entry per
     * published post and recap. Books have no per-book route, so they're
     * only represented via the /books listing.
     */
    public function __invoke(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHour(), function () {
            $staticUrls = collect(['home', 'about', 'projects', 'books', 'blog.index', 'recaps.index'])
                ->map(fn(string $name) => route($name));

            $posts = Post::query()->posts()->published()->get(['type', 'slug', 'updated_at']);
            $recaps = Post::query()->recaps()->published()->get(['type', 'slug', 'updated_at']);

            return view('sitemap', [
                'staticUrls' => $staticUrls,
                'posts' => $posts,
                'recaps' => $recaps,
            ])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
