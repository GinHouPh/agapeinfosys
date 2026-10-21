<?php

namespace App\Services;

use App\Enums\MenuItemType;
use App\Enums\MenuLocation;
use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

/**
 * Resolves the navigation the public site renders.
 *
 * A location renders the first active menu saved for it. When no menu
 * exists the hardcoded fallback in config/navigation.php is used, so the
 * site never renders an empty navigation.
 *
 * The tree is rebuilt on each request from a single eager-loaded query.
 * Menus hold a handful of rows, and a cached tree would go stale whenever a
 * menu is edited outside the admin — correctness wins over one query.
 */
class MenuService
{
    /**
     * The nested navigation for a location.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forLocation(MenuLocation $location): array
    {
        $menu = Menu::query()
            ->active()
            ->forLocation($location)
            ->with(['items' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')])
            ->first();

        if ($menu === null) {
            return $this->fallbackFor($location);
        }

        $items = $menu->items->whereNull('parent_id')->sortBy(
            fn (MenuItem $item): array => [$item->sort_order, $item->id],
        )->values();

        return $this->buildTree($items, $menu->items);
    }

    /**
     * The flat, active items of a location — used by the admin editor.
     *
     * @return Collection<int, MenuItem>
     */
    public function itemsFor(MenuLocation $location): Collection
    {
        return MenuItem::query()
            ->whereHas('menu', fn ($query) => $query->active()->forLocation($location))
            ->with('children')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * The menus saved for a location, active or not.
     *
     * @return Collection<int, Menu>
     */
    public function menusFor(MenuLocation $location): Collection
    {
        return Menu::query()
            ->forLocation($location)
            ->withCount('items')
            ->orderBy('name')
            ->get();
    }

    /**
     * Whether the current request matches a resolved item.
     *
     * @param  array<string, mixed>  $item
     */
    public function isActive(array $item): bool
    {
        $route = $item['route'] ?? null;

        if (! is_string($route) || $route === '') {
            return false;
        }

        return request()->routeIs($route);
    }

    /**
     * The URL a menu item points at.
     */
    public function urlFor(MenuItem $item): ?string
    {
        $value = trim((string) $item->link_value);

        if ($value === '') {
            return null;
        }

        return match ($item->link_type) {
            MenuItemType::Route => $this->urlForRoute($value),
            MenuItemType::Url => $value,
            MenuItemType::Page => $this->urlForPage($value),
            MenuItemType::Record => $this->urlForRecord($value),
            MenuItemType::Category => $this->urlForCategory($value),
            default => null,
        };
    }

    /**
     * The named route a menu item resolves to, used to mark the active link.
     */
    public function routeFor(MenuItem $item): ?string
    {
        $value = trim((string) $item->link_value);

        if ($value === '') {
            return null;
        }

        return match ($item->link_type) {
            MenuItemType::Route => Route::has($value) ? $value : null,
            MenuItemType::Page => $this->routeForPage($value),
            MenuItemType::Record => $this->routeForRecord($value),
            MenuItemType::Category => 'shop.category',
            default => null,
        };
    }

    /**
     * Build the nested tree from a flat list of items.
     *
     * @param  Collection<int, MenuItem>  $roots
     * @param  Collection<int, MenuItem>  $all
     * @return array<int, array<string, mixed>>
     */
    private function buildTree(Collection $roots, Collection $all): array
    {
        return $roots
            ->map(fn (MenuItem $item): array => $this->resolveItem($item, $all))
            ->all();
    }

    /**
     * Turn one item — and its children — into the render-ready shape.
     *
     * @param  Collection<int, MenuItem>  $all
     * @return array<string, mixed>
     */
    private function resolveItem(MenuItem $item, Collection $all): array
    {
        $children = $all
            ->where('parent_id', $item->id)
            ->sortBy(fn (MenuItem $child): array => [$child->sort_order, $child->id])
            ->map(fn (MenuItem $child): array => $this->resolveItem($child, $all))
            ->values()
            ->all();

        return [
            'id' => $item->id,
            'label' => $item->label,
            'url' => $this->urlFor($item),
            'icon' => $item->icon,
            'badge' => $item->badge,
            'css_class' => $item->css_class,
            'target' => $item->target,
            'route' => $this->routeFor($item),
            'children' => $children,
        ];
    }

    /**
     * The hardcoded navigation for a location, normalised to the same shape.
     *
     * @return array<int, array<string, mixed>>
     */
    private function fallbackFor(MenuLocation $location): array
    {
        $fallbacks = config('navigation.fallback.'.$location->value, []);

        return array_map(function (array $fallback): array {
            $route = $fallback['route'] ?? null;

            return [
                'id' => null,
                'label' => $fallback['label'] ?? '',
                'url' => is_string($route) && Route::has($route) ? route($route) : null,
                'icon' => $fallback['icon'] ?? null,
                'badge' => null,
                'css_class' => null,
                'target' => '_self',
                'route' => is_string($route) ? $route : null,
                'children' => [],
            ];
        }, $fallbacks);
    }

    /**
     * The URL of a named route.
     */
    private function urlForRoute(string $value): ?string
    {
        if (! Route::has($value)) {
            return null;
        }

        return route($value);
    }

    /**
     * The URL of a builder-managed page.
     */
    private function urlForPage(string $slug): ?string
    {
        $page = Page::query()->published()->where('slug', $slug)->first();

        return $page?->targetUrl();
    }

    /**
     * The named route a builder-managed page renders at.
     */
    private function routeForPage(string $slug): ?string
    {
        $page = Page::query()->published()->where('slug', $slug)->first();

        return $page?->flagshipRoute();
    }

    /**
     * The URL of a published record, addressed as "alias:slug".
     */
    private function urlForRecord(string $value): ?string
    {
        [$alias, $slug] = array_pad(explode(':', $value, 2), 2, null);

        if ($slug === null || $slug === '') {
            return null;
        }

        $definition = config('navigation.record_models.'.$alias);

        if (! is_array($definition)) {
            return null;
        }

        $model = $this->recordFor($definition['model'] ?? null, $slug);

        if ($model === null) {
            return null;
        }

        return route($definition['route'], $model);
    }

    /**
     * The named route of a published record, addressed as "alias:slug".
     */
    private function routeForRecord(string $value): ?string
    {
        [$alias] = array_pad(explode(':', $value, 2), 2, null);

        $definition = config('navigation.record_models.'.$alias);

        if (! is_array($definition)) {
            return null;
        }

        return $definition['route'] ?? null;
    }

    /**
     * The URL of a shop category.
     */
    private function urlForCategory(string $slug): ?string
    {
        $category = Category::query()->where('slug', $slug)->first();

        return $category === null ? null : route('shop.category', $category);
    }

    /**
     * Find a published record of a given model by slug.
     *
     * @param  class-string<Model>|null  $model
     */
    private function recordFor(?string $model, string $slug): ?Model
    {
        if ($model === null || ! class_exists($model)) {
            return null;
        }

        $query = $model::query()->where('slug', $slug);

        if (method_exists($model, 'scopePublished')) {
            $query->published();
        }

        return $query->first();
    }
}
