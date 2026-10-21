<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Puts files into the media library and takes them out again.
 */
class MediaService
{
    /**
     * Store an uploaded file and record it in the library.
     */
    public function store(UploadedFile $file, ?string $directory = null, ?int $userId = null): Media
    {
        $directory = trim($directory ?? 'uploads', '/');
        $name = Str::uuid()->toString().'.'.strtolower($file->extension());

        $path = $file->storeAs($directory, $name, 'public');

        [$width, $height] = $this->dimensions($file);

        return Media::query()->create([
            'disk' => 'public',
            'directory' => $directory,
            'filename' => $name,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize() ?: 0,
            'width' => $width,
            'height' => $height,
            'alt_text' => null,
            'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: null,
            'is_public' => true,
            'created_by' => $userId,
        ]);
    }

    /**
     * Record a file that is already on disk.
     */
    public function record(string $path, ?string $mimeType = null, ?int $userId = null): ?Media
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $directory = trim(dirname($path), '.');

        return Media::query()->create([
            'disk' => 'public',
            'directory' => $directory === '' ? 'uploads' : $directory,
            'filename' => basename($path),
            'original_name' => basename($path),
            'mime_type' => $mimeType ?? $disk->mimeType($path),
            'size' => $disk->size($path),
            'is_public' => true,
            'created_by' => $userId,
        ]);
    }

    /**
     * Delete a file and its record.
     */
    public function delete(Media $media): void
    {
        $disk = Storage::disk($media->disk);

        if ($disk->exists($media->path())) {
            $disk->delete($media->path());
        }

        $media->delete();
    }

    /**
     * The pixel dimensions of an image, when it is one.
     *
     * @return array{0: ?int, 1: ?int}
     */
    private function dimensions(UploadedFile $file): array
    {
        if (! str_starts_with((string) $file->getClientMimeType(), 'image/')) {
            return [null, null];
        }

        $size = @getimagesize($file->getRealPath());

        if ($size === false) {
            return [null, null];
        }

        return [(int) $size[0], (int) $size[1]];
    }
}
