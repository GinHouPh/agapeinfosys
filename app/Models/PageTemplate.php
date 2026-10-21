<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Database\Factories\PageTemplateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A starter layout a new page can be created from.
 */
class PageTemplate extends Model
{
    /** @use HasFactory<PageTemplateFactory> */
    use GeneratesSlug, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'thumbnail',
        'schema',
        'is_active',
        'usage_count',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'schema' => 'array',
            'is_active' => 'boolean',
            'usage_count' => 'integer',
        ];
    }

    /**
     * The slug is built from the template name, not a title.
     *
     * @var list<string>
     */
    protected $slugSource = ['name'];

    /**
     * The admin who saved the template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Templates that are switched on.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * The widget blocks this template holds.
     *
     * @return array<int, array<string, mixed>>
     */
    public function blocks(): array
    {
        $blocks = $this->schema['blocks'] ?? [];

        return is_array($blocks) ? $blocks : [];
    }
}
