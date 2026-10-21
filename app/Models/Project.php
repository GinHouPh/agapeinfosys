<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasTranslations;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use GeneratesSlug, HasFactory, HasSeoMeta, HasTranslations;

    protected $fillable = [
        'title',
        'slug',
        'client',
        'category',
        'service_type',
        'excerpt',
        'body',
        'cover_image',
        'gallery',
        'stack',
        'outcomes',
        'project_url',
        'year',
        'sort_order',
        'is_featured',
        'is_published',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'stack' => 'array',
            'outcomes' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
            'year' => 'integer',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('id');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Case studies related to this one, matched on category then service type.
     *
     * @return Collection<int, Project>
     */
    public function related(int $limit = 3)
    {
        return static::query()
            ->published()
            ->where('id', '!=', $this->id)
            ->orderByRaw('CASE WHEN category = ? THEN 0 ELSE 1 END', [$this->category])
            ->take($limit)
            ->get();
    }
}
