<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Database\Factories\ComponentTemplateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A reusable block an author can drop into any page.
 *
 * The template stores a widget schema exactly as the builder writes it, so
 * inserting one is the same operation as pasting a section.
 */
class ComponentTemplate extends Model
{
    /** @use HasFactory<ComponentTemplateFactory> */
    use GeneratesSlug, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'widget',
        'description',
        'icon',
        'schema',
        'slots',
        'thumbnail',
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
            'slots' => 'array',
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

    /**
     * The named holes an author fills when inserting the template.
     *
     * @return array<int, array{name: string, label: string, type: string, default?: mixed}>
     */
    public function slotDefinitions(): array
    {
        $slots = $this->slots ?? [];

        if (! is_array($slots)) {
            return [];
        }

        return array_values(array_filter($slots, fn ($slot): bool => is_array($slot) && is_string($slot['name'] ?? null)));
    }
}
