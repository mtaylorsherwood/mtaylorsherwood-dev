<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookCategory;
use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $title
 * @property string $author
 * @property BookCategory $category
 * @property string $genre
 * @property int $pages
 * @property string|null $series
 * @property bool $is_reread
 * @property string|null $review
 * @property Carbon|null $started_at
 * @property Carbon|null $finished_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'author', 'category', 'genre', 'pages', 'series', 'is_reread', 'review', 'started_at', 'finished_at'])]
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    /**
     * Cache keys derived from the full book list, flushed together whenever
     * a book is created, updated, or deleted. Writes are rare on a
     * single-author site, so a blunt "flush everything" is simpler than
     * surgical invalidation and costs nothing in practice.
     *
     * @var array<int, string>
     */
    private const CACHE_KEYS = [
        'books:site:index',
        'books:dashboard:index',
        'books:distinct-genres',
        'books:distinct-series',
    ];

    /**
     * Invalidate the cached book list and lookups whenever a book changes.
     */
    protected static function booted(): void
    {
        static::saved(fn() => self::flushCaches());
        static::deleted(fn() => self::flushCaches());
    }

    /**
     * Flush every cache key derived from the book list.
     */
    private static function flushCaches(): void
    {
        foreach (self::CACHE_KEYS as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => BookCategory::class,
            'pages' => 'integer',
            'is_reread' => 'boolean',
            'started_at' => 'date',
            'finished_at' => 'date',
        ];
    }

    /**
     * Scope a query to only books that have not been started yet.
     *
     * @param  Builder<Book>  $query
     * @return Builder<Book>
     */
    public function scopeToBeRead(Builder $query): Builder
    {
        return $query->whereNull('started_at');
    }

    /**
     * Scope a query to only books that are still being read.
     *
     * @param  Builder<Book>  $query
     * @return Builder<Book>
     */
    public function scopeCurrentlyReading(Builder $query): Builder
    {
        return $query->whereNotNull('started_at')->whereNull('finished_at');
    }

    /**
     * Scope a query to only books that have been finished.
     *
     * @param  Builder<Book>  $query
     * @return Builder<Book>
     */
    public function scopeFinished(Builder $query): Builder
    {
        return $query->whereNotNull('finished_at');
    }
}
