<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores validated image uploads on the public disk and prunes replaced files.
 */
trait HandlesImageUploads
{
    /**
     * Persist an uploaded image, returning its storage path or null when absent.
     */
    protected function storeUploadedImage(?UploadedFile $file, string $directory = 'uploads'): ?string
    {
        if ($file === null) {
            return null;
        }

        $name = Str::uuid()->toString().'.'.strtolower($file->extension());

        return $file->storeAs($directory, $name, 'public');
    }

    /**
     * Swap in a fresh upload and delete the file it replaces.
     */
    protected function replaceUploadedImage(?UploadedFile $file, ?string $current, string $directory = 'uploads'): ?string
    {
        $stored = $this->storeUploadedImage($file, $directory);

        if ($stored === null) {
            return $current;
        }

        if ($current !== null && $current !== '' && Storage::disk('public')->exists($current)) {
            Storage::disk('public')->delete($current);
        }

        return $stored;
    }

    /**
     * Remove a media file once its owning record is deleted.
     */
    protected function forgetUploadedImage(?string $path): void
    {
        if ($path !== null && $path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
