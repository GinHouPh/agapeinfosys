<?php

namespace Database\Factories;

use App\Enums\MenuItemType;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'label' => ucfirst($this->faker->words(2, true)),
            'link_type' => MenuItemType::Route,
            'link_value' => 'home',
            'icon' => null,
            'badge' => null,
            'css_class' => null,
            'target' => '_self',
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    /**
     * Nest the item under a parent.
     */
    public function childOf(MenuItem $parent): static
    {
        return $this->state(fn (): array => [
            'menu_id' => $parent->menu_id,
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * Point the item at an external URL.
     */
    public function external(string $url = 'https://example.com'): static
    {
        return $this->state(fn (): array => [
            'link_type' => MenuItemType::Url,
            'link_value' => $url,
            'target' => '_blank',
        ]);
    }
}
