<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\CloudflareImageUrl;
use Database\Factories\ImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $path
 * @property string $mime_type
 * @property int $width
 * @property int $height
 * @property int|null $post_id
 * @property int|null $project_id
 * @property int|null $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['path', 'mime_type', 'width', 'height', 'post_id', 'project_id', 'position'])]
class Image extends Model
{
    /** @use HasFactory<ImageFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'position' => 'integer',
        ];
    }

    /**
     * Delete the underlying storage object whenever an image row is deleted.
     */
    protected static function booted(): void
    {
        static::deleted(function (Image $image) {
            $diskName = config('filesystems.images_disk');
            Storage::disk(is_string($diskName) ? $diskName : 'r2')->delete($image->path);
        });
    }

    /**
     * The post this image belongs to, if it has been attached to one.
     *
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * The project this image belongs to, if it has been attached to one.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Build a Cloudflare-transformed URL for displaying this image.
     */
    public function url(int $width = 1200, int $quality = 80): string
    {
        return CloudflareImageUrl::make($this->path, width: $width, quality: $quality);
    }
}
