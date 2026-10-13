<?php

namespace App\Models;

use App\Enums\PageStatus;
use App\Models\Concerns\GeneratesSlug;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A builder-managed page: an ordered list of widget blocks stored as JSON.
 */
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use GeneratesSlug, HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'status',
        'meta_description',
        'schema',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
            'schema' => 'array',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PageStatus::Published);
    }

    /**
     * The ordered widget blocks of this page.
     *
     * Stored JSON is normalised on the way out: every block is guaranteed a
     * widget name, a settings array and an id, so a hand-edited or legacy
     * schema can never break the editor canvas. Missing ids are derived from
     * the block's position, which keeps them stable between renders until the
     * next save persists them.
     *
     * @return array<int, array{id: string, widget: string, settings: array<string, mixed>}>
     */
    public function blocks(): array
    {
        $blocks = $this->schema['blocks'] ?? [];

        if (! is_array($blocks)) {
            return [];
        }

        $clean = [];

        foreach (array_values($blocks) as $index => $block) {
            if (! is_array($block)) {
                continue;
            }

            $widget = $block['widget'] ?? null;

            if (! is_string($widget) || $widget === '') {
                continue;
            }

            $id = $block['id'] ?? null;

            $clean[] = [
                'id' => is_string($id) && $id !== ''
                    ? $id
                    : $index.'-'.$widget,
                'widget' => $widget,
                'settings' => is_array($block['settings'] ?? null) ? $block['settings'] : [],
            ];
        }

        return $clean;
    }
}
