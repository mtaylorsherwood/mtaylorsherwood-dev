<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'post_content',
        'published_at'
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date:Y-m-d',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Publish this particular post.
     *
     * @return void
     */
    public function publish(): void
    {
        if ($this->published_at !== null) {
            return;
        }

        $this->published_at = now();
        $this->save();
    }

    /**
     * Check to see if the post has been published.
     *
     * @return bool
     */
    public function published(): bool
    {
        return $this->published_at !== null;
    }
}
