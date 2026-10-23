<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    /**
     * Display the reading list, alongside recent reading recaps.
     */
    public function index(): Response
    {
        $books = Cache::rememberForever('books:site:index', fn() => Book::query()
            ->whereNotNull('started_at')
            ->orderByDesc('finished_at')
            ->orderByDesc('started_at')
            ->get([
                'id', 'title', 'author', 'category', 'genre', 'pages',
                'series', 'is_reread', 'review', 'started_at', 'finished_at',
            ])
            ->toArray());

        $recaps = Post::query()
            ->recaps()
            ->published()
            ->orderByDesc('published_at')
            ->take(5)
            ->get(['id', 'title', 'slug', 'starts_at', 'ends_at', 'published_at']);

        return Inertia::render('site/books/Index', [
            'books' => $books,
            'recaps' => $recaps,
        ]);
    }
}
