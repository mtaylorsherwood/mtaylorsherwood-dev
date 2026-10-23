<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Images\StoreImageRequest;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Upload an image, storing it under a content-hashed path so re-uploading
     * the same file is a safe no-op and the object can be cached forever.
     */
    public function store(StoreImageRequest $request): JsonResponse
    {
        $file = $request->file('image');

        $contents = $file->get();

        if ($contents === false) {
            abort(422, 'Unable to read the uploaded file.');
        }

        $hash = hash('sha256', $contents);
        $path = "images/{$hash}.{$file->extension()}";
        $diskName = config('filesystems.images_disk');
        $disk = Storage::disk(is_string($diskName) ? $diskName : 'r2');

        if (!$disk->exists($path)) {
            $disk->put($path, $contents, [
                'CacheControl' => 'public, max-age=31536000, immutable',
            ]);
        }

        $dimensions = getimagesize($file->getRealPath());

        if ($dimensions === false) {
            abort(422, 'Unable to read image dimensions.');
        }

        $image = Image::query()->firstOrCreate(
            ['path' => $path],
            [
                'mime_type' => $file->getMimeType(),
                'width' => $dimensions[0],
                'height' => $dimensions[1],
            ],
        );

        return response()->json([
            'id' => $image->id,
            'path' => $image->path,
            'width' => $image->width,
            'height' => $image->height,
            'url' => $image->url(),
        ], 201);
    }
}
