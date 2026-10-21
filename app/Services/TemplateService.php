<?php

namespace App\Services;

use App\Builder\WidgetRegistry;
use App\Models\ComponentTemplate;
use App\Models\PageTemplate;
use Illuminate\Support\Collection;

/**
 * Turns saved templates back into blocks the builder can drop onto a page.
 */
class TemplateService
{
    public function __construct(private readonly WidgetRegistry $registry) {}

    /**
     * The active component templates, grouped by category.
     *
     * @return Collection<string, Collection<int, ComponentTemplate>>
     */
    public function componentCategories(): Collection
    {
        return ComponentTemplate::query()
            ->active()
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (ComponentTemplate $template): string => $template->category ?: 'General');
    }

    /**
     * The active page templates.
     *
     * @return Collection<int, PageTemplate>
     */
    public function pageTemplates(): Collection
    {
        return PageTemplate::query()
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * The blocks a component inserts, with its slots filled in.
     *
     * @param  array<string, mixed>  $slotValues
     * @return array<int, array<string, mixed>>
     */
    public function insertComponent(ComponentTemplate $template, array $slotValues = []): array
    {
        $slots = $this->resolveSlots($template, $slotValues);

        $blocks = $this->substitute($template->blocks(), $slots);

        $template->increment('usage_count');

        return $this->refreshIds($blocks);
    }

    /**
     * The blocks a page template seeds a new page with.
     *
     * @return array<int, array<string, mixed>>
     */
    public function seedPage(PageTemplate $template): array
    {
        $template->increment('usage_count');

        return $this->refreshIds($template->blocks());
    }

    /**
     * The value of each slot, falling back to the slot's own default.
     *
     * @param  array<string, mixed>  $slotValues
     * @return array<string, string>
     */
    public function resolveSlots(ComponentTemplate $template, array $slotValues = []): array
    {
        $resolved = [];

        foreach ($template->slotDefinitions() as $slot) {
            $name = $slot['name'];
            $value = $slotValues[$name] ?? ($slot['default'] ?? '');

            $resolved[$name] = is_scalar($value) ? (string) $value : '';
        }

        return $resolved;
    }

    /**
     * Replace every slot placeholder in a block tree.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, string>  $slots
     * @return array<int, array<string, mixed>>
     */
    private function substitute(array $blocks, array $slots): array
    {
        foreach ($blocks as $index => $block) {
            if (! is_array($block)) {
                continue;
            }

            if (is_array($block['settings'] ?? null)) {
                $blocks[$index]['settings'] = $this->substituteValue($block['settings'], $slots);
            }

            if (is_array($block['children'] ?? null)) {
                $blocks[$index]['children'] = $this->substitute($block['children'], $slots);
            }
        }

        return $blocks;
    }

    /**
     * Replace slot placeholders in one value.
     *
     * @param  array<string, string>  $slots
     */
    private function substituteValue(mixed $value, array $slots): mixed
    {
        if (is_string($value)) {
            foreach ($slots as $name => $replacement) {
                $value = str_replace('{{'.$name.'}}', $replacement, $value);
            }

            return $value;
        }

        if (is_array($value)) {
            return array_map(fn ($item): mixed => $this->substituteValue($item, $slots), $value);
        }

        return $value;
    }

    /**
     * Give every block a fresh id so a template can be inserted twice.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    private function refreshIds(array $blocks): array
    {
        foreach ($blocks as $index => $block) {
            if (! is_array($block)) {
                continue;
            }

            $blocks[$index]['id'] = $this->registry::newBlockId();

            if (is_array($block['children'] ?? null)) {
                $blocks[$index]['children'] = $this->refreshIds($block['children']);
            }
        }

        return $blocks;
    }
}
