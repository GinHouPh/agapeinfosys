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
     * The reserved slugs that take over a named public route when published.
     *
     * @return array<string, string>
     */
    public static function flagshipRoutes(): array
    {
        return config('builder.flagship', []);
    }

    /**
     * The reserved slugs that render every detail page of a record type.
     *
     * @return array<string, array{label: string, model: class-string<Model>, scope: string, route: ?string, pattern: string}>
     */
    public static function recordTemplates(): array
    {
        return config('builder.record_templates', []);
    }

    /**
     * Whether this slug hijacks a flagship marketing route.
     */
    public function isFlagship(): bool
    {
        return array_key_exists($this->slug, static::flagshipRoutes());
    }

    /**
     * The named route this flagship slug takes over, if any.
     */
    public function flagshipRoute(): ?string
    {
        return static::flagshipRoutes()[$this->slug] ?? null;
    }

    /**
     * The single-record template definition bound to this slug, if any.
     *
     * @return array{label: string, model: class-string<Model>, scope: string, route: ?string, pattern: string}|null
     */
    public function template(): ?array
    {
        return static::recordTemplates()[$this->slug] ?? null;
    }

    /**
     * Whether this slug is reserved: it has a public URL owned by its own
     * controller rather than living under /pages/{slug}.
     */
    public function isReserved(): bool
    {
        return $this->isFlagship() || $this->template() !== null;
    }

    /**
     * Where visitors see this page, or null when it has no public URL yet.
     *
     * Flagship pages render at their own route once published; templates have
     * no single URL, so they link nowhere until a record is picked.
     */
    public function publicUrl(): ?string
    {
        if ($this->template() !== null || ! $this->status->isPublished()) {
            return null;
        }

        return $this->targetUrl();
    }

    /**
     * The URL this page is destined for, ignoring publish status.
     *
     * Record templates still return null — they render other URLs.
     */
    public function targetUrl(): ?string
    {
        if ($this->template() !== null) {
            return null;
        }

        return $this->isFlagship() ? route($this->flagshipRoute()) : route('pages.show', $this);
    }

    /**
     * The public path this page occupies, for display in the admin index.
     */
    public function targetPath(): string
    {
        $url = $this->targetUrl();

        if ($url === null) {
            return (string) ($this->template()['pattern'] ?? '/pages/'.$this->slug);
        }

        $path = (string) parse_url($url, PHP_URL_PATH);

        return $path === '' ? '/' : $path;
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
     * @return array<int, array{id: string, widget: string, settings: array<string, mixed>, column?: int, children?: array<int, mixed>}>
     */
    public function blocks(): array
    {
        $blocks = $this->schema['blocks'] ?? [];

        if (! is_array($blocks)) {
            return [];
        }

        return $this->normaliseBlocks($blocks, 0);
    }

    /**
     * Walk one level of the tree, filling in ids and dropping malformed rows.
     *
     * @param  array<array-key, mixed>  $blocks
     * @return array<int, array<string, mixed>>
     */
    private function normaliseBlocks(array $blocks, int $depth): array
    {
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

            $entry = [
                'id' => is_string($id) && $id !== ''
                    ? $id
                    : $index.'-'.$widget,
                'widget' => $widget,
                'settings' => is_array($block['settings'] ?? null) ? $block['settings'] : [],
            ];

            if ($depth > 0 && is_numeric($block['column'] ?? null)) {
                $entry['column'] = (int) $block['column'];
            }

            if (is_array($block['children'] ?? null)) {
                $entry['children'] = $this->normaliseBlocks($block['children'], $depth + 1);
            }

            $clean[] = $entry;
        }

        return $clean;
    }
}
