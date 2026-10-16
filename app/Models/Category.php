<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * A storefront category. Categories nest up to three levels:
 * market (root) → category → subcategory.
 */
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use GeneratesSlug, HasFactory;

    /**
     * Attributes consulted to build the slug when none is supplied.
     *
     * @var list<string>
     */
    protected $slugSource = ['name'];

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Whether this category is a top-level market.
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * This category plus every descendant id, for product listings.
     *
     * @return list<int>
     */
    public function descendantIds(): array
    {
        $this->loadMissing('children.children');

        $ids = [$this->getKey()];

        foreach ($this->children as $child) {
            $ids[] = $child->getKey();

            foreach ($child->children as $grandchild) {
                $ids[] = $grandchild->getKey();
            }
        }

        return $ids;
    }

    /**
     * Whether this category is the given ancestor or sits beneath it.
     */
    public function isDescendantOf(Category $ancestor): bool
    {
        $node = $this;

        while ($node !== null) {
            if ($node->getKey() === $ancestor->getKey()) {
                return true;
            }

            $node = $node->parent;
        }

        return false;
    }

    /**
     * The chain from the root market down to this category's parent.
     *
     * @return Collection<int, Category>
     */
    public function ancestors(): Collection
    {
        $ancestors = new Collection;
        $node = $this->parent;

        while ($node !== null) {
            $ancestors->prepend($node);
            $node = $node->parent;
        }

        return $ancestors;
    }

    /**
     * Every category in depth-first tree order, paired with its depth.
     *
     * @param  Builder<Category>|null  $query
     * @return Collection<int, array{category: Category, depth: int}>
     */
    public static function flattenedTree(?Builder $query = null): Collection
    {
        $categories = ($query ?? static::query())
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        /** @var array<int, list<Category>> $byParent */
        $byParent = [];

        foreach ($categories as $category) {
            $byParent[$category->parent_id ?? 0][] = $category;
        }

        $flat = new Collection;

        $walk = function (int $parentKey, int $depth) use (&$walk, $flat, $byParent): void {
            foreach ($byParent[$parentKey] ?? [] as $category) {
                $flat->push(['category' => $category, 'depth' => $depth]);

                $walk($category->getKey(), $depth + 1);
            }
        };

        $walk(0, 0);

        return $flat;
    }

    /**
     * Depth-indented id/label pairs for admin selects, optionally omitting a
     * category and its whole subtree (used to prevent parent cycles).
     *
     * @return array<int, string>
     */
    public static function selectOptions(?Category $exclude = null): array
    {
        return static::flattenedTree()
            ->reject(fn (array $row): bool => $exclude !== null && $row['category']->isDescendantOf($exclude))
            ->mapWithKeys(fn (array $row): array => [
                $row['category']->getKey() => str_repeat('— ', $row['depth']).$row['category']->name,
            ])
            ->all();
    }
}
