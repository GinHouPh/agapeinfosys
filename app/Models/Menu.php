<?php

namespace App\Models;

use App\Enums\MenuLocation;
use App\Models\Concerns\GeneratesSlug;
use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A named menu rendered at one location of the public site.
 */
class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use GeneratesSlug, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'location',
        'description',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'location' => MenuLocation::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * The slug is built from the menu name, not a title.
     *
     * @var list<string>
     */
    protected $slugSource = ['name'];

    /**
     * Every item belonging to this menu.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Only the top-level items.
     */
    public function rootItems(): HasMany
    {
        return $this->items()->whereNull('parent_id');
    }

    /**
     * Menus that are switched on.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * The menu that renders a location, if one is saved and switched on.
     */
    public function scopeForLocation(Builder $query, MenuLocation $location): Builder
    {
        return $query->where('location', $location);
    }
}
