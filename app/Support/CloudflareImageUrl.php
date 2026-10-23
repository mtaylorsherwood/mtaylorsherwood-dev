<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class CloudflareImageUrl
{
    /**
     * Build a Cloudflare Image Transformations URL for the given storage path,
     * resizing and re-encoding (auto WebP/AVIF) on the fly at Cloudflare's edge.
     *
     * Falls back to the plain, untransformed storage URL when no
     * CLOUDFLARE_IMAGES_DOMAIN is configured, so images still render in
     * environments where the custom domain hasn't been wired up yet.
     *
     * @see https://developers.cloudflare.com/images/transform-images/
     */
    public static function make(string $path, int $width, int $quality = 80, string $format = 'auto'): string
    {
        $path = ltrim($path, '/');
        $domain = config('services.cloudflare_images.domain');

        if (!is_string($domain) || $domain === '') {
            $diskName = config('filesystems.images_disk');

            return Storage::disk(is_string($diskName) ? $diskName : 'r2')->url($path);
        }

        $options = "width={$width},quality={$quality},format={$format}";

        return "https://{$domain}/cdn-cgi/image/{$options}/{$path}";
    }
}
