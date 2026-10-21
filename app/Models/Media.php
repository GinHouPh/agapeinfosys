<?php

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * One file in the media library.
 */
class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    protected $fillable = [
        'disk',
        'directory',
        'filename',
        'original_name',
        'mime_type',
        'size',
        'width',
        'height',
        'alt_text',
        'title',
        'focal_x',
        'focal_y',
        'is_public',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'focal_x' => 'integer',
            'focal_y' => 'integer',
            'is_public' => 'boolean',
        ];
    }

    /**
     * The admin who uploaded the file.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Only images.
     */
    public function scopeImages(Builder $query): Builder
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * Only files that are not images.
     */
    public function scopeDocuments(Builder $query): Builder
    {
        return $query->where('mime_type', 'not like', 'image/%');
    }

    /**
     * The path of the file on its disk.
     */
    public function path(): string
    {
        return trim($this->directory.'/'.$this->filename, '/');
    }

    /**
     * The address the file is served at.
     */
    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path());
    }

    /**
     * Whether the file is an image.
     */
    public function isImage(): bool
    {
        return is_string($this->mime_type) && str_starts_with($this->mime_type, 'image/');
    }

    /**
     * The size of the file, in a form a person can read.
     */
    public function readableSize(): string
    {
        $bytes = (int) $this->size;

        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024) {
                return round($bytes, 1).' '.$unit;
            }

            $bytes /= 1024;
        }

        return round($bytes, 1).' TB';
    }
}
