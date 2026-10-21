<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MenuLocation;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class NavigationController extends Controller
{
    /**
     * Every menu, grouped by the location it renders at.
     */
    public function index(MenuService $menus): View
    {
        $locations = collect(MenuLocation::cases())
            ->mapWithKeys(fn (MenuLocation $location): array => [
                $location->value => [
                    'location' => $location,
                    'menus' => $menus->menusFor($location),
                ],
            ]);

        return view('admin.navigation.index', compact('locations'));
    }

    /**
     * Create a menu for a location.
     */
    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $menu = Menu::query()->create($request->validated());

        return redirect()
            ->route('admin.navigation.edit', $menu)
            ->with('status', 'Menu created — add its items below.');
    }

    /**
     * Edit a menu and the items it renders.
     */
    public function edit(Menu $menu): View
    {
        $menu->load('items');

        $rootItems = $menu->items->whereNull('parent_id');
        $parentOptions = $rootItems->pluck('label', 'id');

        return view('admin.navigation.edit', compact('menu', 'rootItems', 'parentOptions'));
    }

    /**
     * Update a menu's details.
     */
    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $menu->update($request->validated());

        return redirect()
            ->route('admin.navigation.edit', $menu)
            ->with('status', 'Menu updated.');
    }

    /**
     * Delete a menu together with its items.
     */
    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()
            ->route('admin.navigation.index')
            ->with('status', 'Menu deleted.');
    }
}
