<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;

class MenuItemController extends Controller
{
    /**
     * Add an item to a menu.
     */
    public function store(StoreMenuItemRequest $request, Menu $menu): RedirectResponse
    {
        $menu->items()->create($request->validated());

        return redirect()
            ->route('admin.navigation.edit', $menu)
            ->with('status', 'Menu item added.');
    }

    /**
     * Update an item.
     */
    public function update(UpdateMenuItemRequest $request, Menu $menu, MenuItem $item): RedirectResponse
    {
        $this->guardItem($menu, $item);

        $item->update($request->validated());

        return redirect()
            ->route('admin.navigation.edit', $menu)
            ->with('status', 'Menu item updated.');
    }

    /**
     * Delete an item and anything nested under it.
     */
    public function destroy(Menu $menu, MenuItem $item): RedirectResponse
    {
        $this->guardItem($menu, $item);

        $item->children()->update(['parent_id' => null]);
        $item->delete();

        return redirect()
            ->route('admin.navigation.edit', $menu)
            ->with('status', 'Menu item deleted.');
    }

    /**
     * Make sure the item belongs to the menu in the route.
     */
    private function guardItem(Menu $menu, MenuItem $item): void
    {
        abort_unless($item->menu_id === $menu->id, 404);
    }
}
