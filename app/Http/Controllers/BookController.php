<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Books\FinishBookRequest;
use App\Http\Requests\Books\StartReadingRequest;
use App\Http\Requests\Books\StoreBookRequest;
use App\Http\Requests\Books\UpdateBookRequest;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    /**
     * Display all logged books.
     */
    public function index(): Response
    {
        $books = Cache::rememberForever(
            'books:dashboard:index',
            fn() => Book::query()->orderByDesc('finished_at')->orderByDesc('started_at')->get()->toArray(),
        );

        return Inertia::render('books/Index', [
            'books' => $books,
        ]);
    }

    /**
     * Show the form for adding a book.
     */
    public function create(): Response
    {
        return Inertia::render('books/Create', [
            'genres' => $this->distinctGenres(),
            'series' => $this->distinctSeries(),
        ]);
    }

    /**
     * Store a newly logged book.
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        Book::create($request->validated());

        $this->flushBookCaches();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Book added.')]);

        return to_route('dashboard.books.index');
    }

    /**
     * Show the form for editing a book.
     */
    public function edit(Book $book): Response
    {
        return Inertia::render('books/Edit', [
            'book' => $book,
            'genres' => $this->distinctGenres(),
            'series' => $this->distinctSeries(),
        ]);
    }

    /**
     * Update the given book.
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $book->update($request->validated());

        $this->flushBookCaches();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Book updated.')]);

        return to_route('dashboard.books.index');
    }

    /**
     * Delete the given book.
     */
    public function destroy(Book $book): RedirectResponse
    {
        $book->delete();

        $this->flushBookCaches();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Book deleted.')]);

        return to_route('dashboard.books.index');
    }

    /**
     * Move a to-be-read book into currently reading.
     */
    public function start(StartReadingRequest $request, Book $book): RedirectResponse
    {
        $book->update(['started_at' => $request->validated('started_at')]);

        $this->flushBookCaches();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Marked as currently reading.')]);

        return to_route('dashboard.books.index');
    }

    /**
     * Mark a currently reading book as finished.
     */
    public function finish(FinishBookRequest $request, Book $book): RedirectResponse
    {
        $book->update(['finished_at' => $request->validated('finished_at')]);

        $this->flushBookCaches();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Book finished.')]);

        return to_route('dashboard.books.index');
    }

    /**
     * Forget the cached book listings so they reflect the latest data.
     */
    private function flushBookCaches(): void
    {
        Cache::forget('books:dashboard:index');
        Cache::forget('books:site:index');
    }

    /**
     * Get the distinct genres already in use, for suggesting existing values.
     *
     * @return array<int, string>
     */
    private function distinctGenres(): array
    {
        return Cache::rememberForever('books:distinct-genres', function () {
            /** @var array<int, string> $genres */
            $genres = Book::query()->select('genre')->distinct()->orderBy('genre')->pluck('genre')->all();

            return $genres;
        });
    }

    /**
     * Get the distinct series already in use, for suggesting existing values.
     *
     * @return array<int, string>
     */
    private function distinctSeries(): array
    {
        return Cache::rememberForever('books:distinct-series', function () {
            /** @var array<int, string> $series */
            $series = Book::query()->whereNotNull('series')->select('series')->distinct()->orderBy('series')->pluck('series')->all();

            return $series;
        });
    }
}
