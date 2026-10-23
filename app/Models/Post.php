<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PostCategory;
use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Support\CloudflareImageUrl;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use League\CommonMark\CommonMarkConverter;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property PostType $type
 * @property PostCategory|null $category
 * @property int|null $featured_image_id
 * @property int|null $project_id
 * @property string $body
 * @property PostStatus $status
 * @property Carbon|null $published_at
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'slug', 'type', 'category', 'featured_image_id', 'project_id', 'body', 'starts_at', 'ends_at'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /**
     * Invalidate the cached rendered HTML whenever a post is saved or deleted.
     */
    protected static function booted(): void
    {
        static::saved(fn(Post $post) => Cache::forget("posts:{$post->id}:html"));
        static::deleted(fn(Post $post) => Cache::forget("posts:{$post->id}:html"));
    }

    /**
     * The post's featured/cover image, if one has been set.
     *
     * @return BelongsTo<Image, $this>
     */
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    /**
     * The project this post is associated with, if any.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The images that have been attached to this post, either as the
     * featured image or referenced inline within the Markdown body.
     *
     * @return HasMany<Image, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PostType::class,
            'category' => PostCategory::class,
            'status' => PostStatus::class,
            'published_at' => 'datetime',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    /**
     * Render the markdown body as HTML, rewriting inline images to Cloudflare
     * transformation URLs so they're resized, re-encoded, and edge-cached.
     *
     * The rendered HTML is cached indefinitely (invalidated in `booted()`
     * whenever the post is saved or deleted), since Markdown parsing and the
     * image-rewrite pass are pure functions of the post's own content.
     */
    public function toHtml(): string
    {
        return Cache::rememberForever(
            "posts:{$this->id}:html",
            fn() => $this->renderHtml(),
        );
    }

    /**
     * Actually render the markdown body to HTML. See {@see self::toHtml()}.
     *
     * Raw HTML in the body is escaped rather than passed through, since the
     * rendered output is injected into the page via `v-html` for every site
     * visitor — CommonMark's default (`allow`) would otherwise let a post
     * body inject arbitrary markup/scripts into the public page.
     */
    private function renderHtml(): string
    {
        $converter = new CommonMarkConverter(['html_input' => 'escape']);
        $html = $converter->convert($this->body)->getContent();

        return $this->rewriteImages($html);
    }

    /**
     * A short, plain-text preview of the post, for listing pages. Defaults
     * to a length based on the post's type (see {@see PostType::excerptLength()}),
     * since recaps and project updates need more room than a regular post.
     */
    public function excerpt(?int $length = null): string
    {
        $html = preg_replace('/<\/(p|h1|h2|h3|h4|li|blockquote)>/', ' ', $this->toHtml()) ?? '';
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');

        return Str::limit($text, $length ?? $this->type->excerptLength());
    }

    /**
     * The absolute public URL for this post, routed through `recaps.show`
     * or `blog.show` depending on its type (mirrors the `href()` helper on
     * the blog index page).
     */
    public function url(): string
    {
        return route($this->type === PostType::Recap ? 'recaps.show' : 'blog.show', $this->slug);
    }

    /**
     * Publish the post, either immediately or at a specific (possibly future) datetime.
     */
    public function publish(?Carbon $at = null): void
    {
        $this->status = PostStatus::Published;
        $this->published_at = $at ?? Carbon::now();
        $this->save();
    }

    /**
     * Scope a query to only posts that are published and due (published_at has passed).
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published)
            ->where('published_at', '<=', Carbon::now());
    }

    /**
     * Scope a query to only regular blog posts (everything except recaps,
     * which live at their own /recaps routes).
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePosts(Builder $query): Builder
    {
        return $query->where('type', '!=', PostType::Recap);
    }

    /**
     * Scope a query to only reading recaps.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeRecaps(Builder $query): Builder
    {
        return $query->where('type', PostType::Recap);
    }

    /**
     * Attach every image referenced by this post (its featured image, plus
     * any inline images referenced in the Markdown body) to this post, so
     * they're not swept up by the orphaned-upload pruner.
     */
    public function reconcileImages(): void
    {
        $paths = $this->imagePathsInBody();

        if ($this->featured_image_id !== null) {
            $this->loadMissing('featuredImage');
            $paths[] = $this->featuredImage?->path;
        }

        $paths = array_values(array_unique(array_filter($paths)));

        if ($paths === []) {
            return;
        }

        Image::query()->whereIn('path', $paths)->update(['post_id' => $this->id]);
    }

    /**
     * Extract every uploaded image path referenced within the Markdown body.
     *
     * @return array<int, string>
     */
    private function imagePathsInBody(): array
    {
        preg_match_all('/\((images\/[^\s)]+)\)/', $this->body, $matches);

        return $matches[1];
    }

    /**
     * Rewrite every rendered <img> tag pointing at an uploaded image path to
     * a Cloudflare transformation URL, and add lazy-loading plus width/height
     * attributes (from the images table) to avoid layout shift.
     */
    private function rewriteImages(string $html): string
    {
        if (!str_contains($html, '<img')) {
            return $html;
        }

        preg_match_all('/<img\s[^>]*src="(images\/[^"]+)"[^>]*>/', $html, $srcMatches);
        $paths = array_unique($srcMatches[1]);

        $dimensionsByPath = Image::query()->whereIn('path', $paths)->get(['path', 'width', 'height'])->keyBy('path');

        $rewritten = preg_replace_callback(
            '/<img\s[^>]*src="(images\/[^"]+)"[^>]*>/',
            function (array $match) use ($dimensionsByPath) {
                [$tag, $path] = $match;

                $url = CloudflareImageUrl::make($path, width: 1200);
                $attributes = 'loading="lazy"';

                $dimensions = $dimensionsByPath->get($path);

                if ($dimensions !== null) {
                    $attributes .= " width=\"{$dimensions->width}\" height=\"{$dimensions->height}\"";
                }

                $tag = preg_replace('/src="[^"]+"/', 'src="'.$url.'"', $tag, 1) ?? $tag;

                return str_replace('<img ', "<img {$attributes} ", $tag);
            },
            $html,
        );

        return $rewritten ?? $html;
    }
}
