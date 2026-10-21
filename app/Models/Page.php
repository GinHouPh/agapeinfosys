<?php

namespace App\Models;

use App\Builder\DesignTokens;
use App\Builder\SchemaMigrator;
use App\Enums\PageStatus;
use App\Models\Concerns\GeneratesSlug;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasTranslations;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * A builder-managed page: an ordered list of widget blocks stored as JSON.
 */
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use GeneratesSlug, HasFactory, HasSeoMeta, HasTranslations;

    protected $fillable = [
        'parent_id',
        'template_id',
        'title',
        'slug',
        'status',
        'scheduled_for',
        'sort_order',
        'path',
        'meta_description',
        'token_overrides',
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
            'token_overrides' => 'array',
            'scheduled_for' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    /**
     * The design tokens this page overrides, by scheme.
     *
     * Only tokens the platform knows are kept — a page may change the
     * accent, not invent a colour the site has no name for.
     *
     * @return array<string, array<string, string>>
     */
    public function tokenOverrides(): array
    {
        $overrides = $this->token_overrides ?? [];

        if (! is_array($overrides)) {
            return [];
        }

        $clean = [];

        foreach (['dark', 'light'] as $scheme) {
            $values = is_array($overrides[$scheme] ?? null) ? $overrides[$scheme] : [];

            foreach ($values as $name => $value) {
                if (! DesignTokens::isColor($name) || ! is_string($value) || $value === '') {
                    continue;
                }

                $clean[$scheme][$name] = $value;
            }
        }

        return array_filter($clean);
    }

    /**
     * Keep the public path in step with the page's place in the tree.
     */
    protected static function booted(): void
    {
        static::saving(function (self $page): void {
            $page->path = $page->buildPath();
        });

        static::saved(function (self $page): void {
            $page->syncDescendantPaths();
        });

        // Every layout change keeps the version it replaced, so an author
        // can go back to what they had.
        static::updated(function (self $page): void {
            if ($page->wasChanged('schema')) {
                $page->snapshotRevision();
            }
        });
    }

    /**
     * Keep the version this page just replaced.
     */
    public function snapshotRevision(?string $note = null): PageRevision
    {
        $revision = PageRevision::query()->create([
            'page_id' => $this->getKey(),
            'title' => $this->getOriginal('title') ?: $this->title,
            'schema' => $this->getOriginal('schema'),
            'note' => $note,
            'created_by' => auth()->id(),
        ]);

        $this->trimRevisions();

        return $revision;
    }

    /**
     * Keep only the most recent versions of a page.
     */
    public function trimRevisions(int $keep = 20): void
    {
        $stale = $this->revisions()
            ->orderByDesc('id')
            ->skip($keep)
            ->take(PHP_INT_MAX)
            ->pluck('id');

        if ($stale->isNotEmpty()) {
            PageRevision::query()->whereIn('id', $stale)->delete();
        }
    }

    /**
     * Put a saved version back.
     */
    public function restore(PageRevision $revision): void
    {
        $this->update([
            'title' => $revision->title,
            'schema' => $revision->schema,
        ]);
    }

    /**
     * The page this one sits under.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * The pages nested under this one.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * The saved versions of this page's layout.
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->orderByDesc('id');
    }

    /**
     * An admin URL for this page, addressed by its id.
     *
     * The public route is addressed by its path; the admin is addressed by
     * its number, so a rename never breaks a bookmarked edit screen.
     *
     * @param  array<string, mixed>  $parameters
     */
    public function adminRoute(string $name, array $parameters = []): string
    {
        return route($name, ['page' => $this->getKey()] + $parameters);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('status', PageStatus::Published)
                ->orWhere(function (Builder $query): void {
                    $query->where('status', PageStatus::Scheduled)
                        ->whereNotNull('scheduled_for')
                        ->where('scheduled_for', '<=', now());
                });
        });
    }

    /**
     * Whether the page is reachable by the public right now.
     *
     * A scheduled page goes live on its own once its date arrives, so it
     * counts as live from that moment without anyone touching it.
     */
    public function isLive(): bool
    {
        if ($this->status->isPublished()) {
            return true;
        }

        return $this->status->isScheduled()
            && $this->scheduled_for !== null
            && $this->scheduled_for->isPast();
    }

    /**
     * The public path of this page, built from its ancestors.
     */
    public function buildPath(): string
    {
        $slug = (string) ($this->slug ?: Str::slug((string) $this->title));

        $parent = $this->parent_id === null
            ? null
            : static::query()->find($this->parent_id);

        if ($parent === null || $parent->path === null || $parent->path === '') {
            return $slug;
        }

        return $parent->path.'/'.$slug;
    }

    /**
     * Re-path the pages nested under this one after it moves.
     */
    public function syncDescendantPaths(): void
    {
        foreach ($this->children()->get() as $child) {
            $path = $child->buildPath();

            if ($child->path !== $path) {
                $child->updateQuietly(['path' => $path]);
            }

            $child->syncDescendantPaths();
        }
    }

    /**
     * The ancestors of this page, root first.
     *
     * @return Collection<int, Page>
     */
    public function ancestors(): Collection
    {
        $ancestors = collect();
        $current = $this->parent;
        $guard = 0;

        while ($current !== null && $guard < 10) {
            $ancestors->prepend($current);
            $current = $current->parent;
            $guard++;
        }

        return $ancestors;
    }

    /**
     * The trail from the root down to this page.
     *
     * @return array<int, array{label: string, url: ?string}>
     */
    public function breadcrumb(): array
    {
        $trail = [];

        foreach ($this->ancestors() as $ancestor) {
            $trail[] = [
                'label' => $ancestor->title,
                'url' => $ancestor->targetUrl(),
            ];
        }

        $trail[] = [
            'label' => $this->title,
            'url' => $this->targetUrl(),
        ];

        return $trail;
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
        if ($this->template() !== null || ! $this->isLive()) {
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

        return $this->isFlagship() ? route($this->flagshipRoute()) : route('pages.show', ['page' => $this->path]);
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

        return $this->prepare($blocks);
    }

    /**
     * Normalise a block tree and carry it forward to the widget's current
     * version.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    private function prepare(array $blocks): array
    {
        return app(SchemaMigrator::class)->migrateTree(
            $this->normaliseBlocks($blocks, 0),
        );
    }

    /**
     * The blocks of this page in the visitor's language.
     *
     * @return array<int, array<string, mixed>>
     */
    public function localisedBlocks(): array
    {
        $schema = $this->translatedSchema();

        $blocks = is_array($schema['blocks'] ?? null) ? $schema['blocks'] : [];

        return $this->prepare($blocks);
    }

    /**
     * Walk one level of the tree, filling in ids and dropping malformed rows.
     *
     * @param  array<array-key, mixed>  $blocks
     * @return array<string, mixed>
     */
    protected function normaliseBlocks(array $blocks, int $depth): array
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
                'version' => is_numeric($block['version'] ?? null) ? (int) $block['version'] : 1,
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
