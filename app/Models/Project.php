<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProjectStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use League\CommonMark\CommonMarkConverter;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property ProjectStatus $status
 * @property string $blurb
 * @property string $body
 * @property string|null $website_url
 * @property string|null $github_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'slug', 'status', 'blurb', 'body', 'website_url', 'github_url'])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
        ];
    }

    /**
     * Invalidate the cached rendered HTML whenever a project is saved or deleted.
     */
    protected static function booted(): void
    {
        static::saved(fn(Project $project) => Cache::forget("projects:{$project->id}:html"));
        static::deleted(fn(Project $project) => Cache::forget("projects:{$project->id}:html"));
    }

    /**
     * Scope a query to only ongoing projects.
     *
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeOngoing(Builder $query): Builder
    {
        return $query->where('status', ProjectStatus::Ongoing);
    }

    /**
     * Scope a query to only dormant projects.
     *
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeDormant(Builder $query): Builder
    {
        return $query->where('status', ProjectStatus::Dormant);
    }

    /**
     * The project's gallery images, in display order.
     *
     * @return HasMany<Image, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class)->orderBy('position');
    }

    /**
     * Posts associated with this project (any type), newest first.
     *
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->orderByDesc('published_at');
    }

    /**
     * Render the markdown body as HTML, cached indefinitely (invalidated in
     * `booted()` whenever the project is saved or deleted).
     */
    public function toHtml(): string
    {
        return Cache::rememberForever(
            "projects:{$this->id}:html",
            fn() => (new CommonMarkConverter(['html_input' => 'escape']))->convert($this->body)->getContent(),
        );
    }

    /**
     * Replace the project's gallery with the given images (in order),
     * detaching any previously attached image that's no longer included.
     * Capped at 5 images.
     *
     * @param  array<int, int>  $imageIds
     */
    public function syncImages(array $imageIds): void
    {
        $imageIds = array_slice($imageIds, 0, 5);

        $this->images()->whereNotIn('id', $imageIds)->update(['project_id' => null, 'position' => null]);

        foreach ($imageIds as $position => $imageId) {
            Image::whereKey($imageId)->update(['project_id' => $this->id, 'position' => $position]);
        }
    }
}
