<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    /**
     * Generate the RSS 2.0 feed of the 20 most recent published posts,
     * recaps and garden updates — matching what /blog already shows.
     */
    public function __invoke(): Response
    {
        $posts = Post::query()
            ->published()
            ->orderByDesc('published_at')
            ->limit(20)
            ->get();

        $xml = view('feed', ['posts' => $posts])->render();

        return response($xml, 200, ['Content-Type' => 'application/rss+xml']);
    }
}
