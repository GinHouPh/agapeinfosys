<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Models\Concerns\GeneratesSlug;
use Database\Factories\ShowcaseProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * An ongoing or upcoming project shown on the showcase poster wall.
 */
class ShowcaseProject extends Model
{
    /** @use HasFactory<ShowcaseProjectFactory> */
    use GeneratesSlug, HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'industry',
        'status',
        'tagline',
        'description',
        'poster',
        'stack',
        'expected_launch',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'stack' => 'array',
            'expected_launch' => 'date',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Distinct industries across the published projects, for the filter bar.
     *
     * @return array<int, string>
     */
    public static function industries(): array
    {
        return static::query()
            ->active()
            ->distinct()
            ->orderBy('industry')
            ->pluck('industry')
            ->all();
    }

    /**
     * Public URL of the poster image, when one has been uploaded.
     */
    public function posterUrl(): ?string
    {
        if ($this->poster === null || $this->poster === '') {
            return null;
        }

        return Storage::url($this->poster);
    }
}
