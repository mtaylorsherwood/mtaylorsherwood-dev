<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Post;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard, with content metrics for posts and books.
     */
    public function index(): Response
    {
        $now = Carbon::now();
        $yearStart = $now->copy()->startOfYear();
        $lastYearNow = $now->copy()->subYear();
        $lastYearStart = $lastYearNow->copy()->startOfYear();
        $monthStart = $now->copy()->startOfMonth();
        $lastYearMonthStart = $lastYearNow->copy()->startOfMonth();

        $yearRange = $this->rangeLabel($yearStart, $now);
        $monthRange = $this->rangeLabel($monthStart, $now);

        return Inertia::render('Dashboard', [
            'metrics' => [
                'posts' => [
                    'total' => Post::query()->published()->count(),
                    'comparisons' => [
                        [
                            'label' => 'This year vs last year',
                            'range' => $yearRange,
                            'current' => Post::query()->published()->whereBetween('published_at', [$yearStart, $now])->count(),
                            'previous' => Post::query()->published()->whereBetween('published_at', [$lastYearStart, $lastYearNow])->count(),
                        ],
                    ],
                ],
                'books_read' => [
                    'total' => Book::query()->finished()->count(),
                    'comparisons' => [
                        [
                            'label' => 'This month vs last year',
                            'range' => $monthRange,
                            'current' => Book::query()->whereBetween('finished_at', [$monthStart, $now])->count(),
                            'previous' => Book::query()->whereBetween('finished_at', [$lastYearMonthStart, $lastYearNow])->count(),
                        ],
                        [
                            'label' => 'This year vs last year',
                            'range' => $yearRange,
                            'current' => Book::query()->whereBetween('finished_at', [$yearStart, $now])->count(),
                            'previous' => Book::query()->whereBetween('finished_at', [$lastYearStart, $lastYearNow])->count(),
                        ],
                    ],
                ],
                'books_tbr' => [
                    'total' => Book::query()->toBeRead()->count(),
                    'comparisons' => [],
                ],
            ],
        ]);
    }

    /**
     * A short "Jan 1 – Aug 19" style label describing a to-date range, so the
     * frontend can show what partial period a year/month comparison covers.
     */
    private function rangeLabel(Carbon $start, Carbon $end): string
    {
        return $start->format('M j').' – '.$end->format('M j');
    }
}
