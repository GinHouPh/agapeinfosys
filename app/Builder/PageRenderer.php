<?php

namespace App\Builder;

use App\Models\Page;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ViewErrorBag;

/**
 * Renders a page's block schema to HTML.
 *
 * Used by the public renderer and by the builder's live-preview endpoint, so
 * the canvas always shows exactly what visitors will see.
 */
class PageRenderer
{
    public function __construct(
        private readonly WidgetRegistry $registry,
        private readonly ViewFactory $views,
    ) {}

    /**
     * Render every block of a page in order.
     *
     * @param  array<string, mixed>  $context
     */
    public function render(Page $page, array $context = []): string
    {
        return $this->renderBlocks($page->blocks(), $context);
    }

    /**
     * Render an ordered list of blocks, skipping unknown widgets.
     *
     * Container blocks (sections) render their own children first and pass
     * the result grouped by column, so a page stays a simple list at the top
     * level and nests only where one was deliberately placed.
     *
     * @param  array<int, array{id?: string, widget?: string, settings?: array<string, mixed>, children?: array<int, mixed>, column?: int}>  $blocks
     * @param  array<string, mixed>  $context
     */
    public function renderBlocks(array $blocks, array $context = []): string
    {
        $html = '';

        foreach ($blocks as $block) {
            $name = $block['widget'] ?? '';

            if (! is_string($name) || $name === '') {
                continue;
            }

            $definition = $this->registry->get($name);

            if ($definition === null) {
                continue;
            }

            $blockContext = $context;

            if ($definition->isContainer()) {
                $blockContext['children'] = $this->renderChildren($block, $definition, $context);
            }

            $html .= $this->renderBlock($name, $block['settings'] ?? [], $blockContext);
        }

        return $html;
    }

    /**
     * Render a container's children, bucketed by the column they sit in.
     *
     * A child claiming a column beyond what the section declares falls back to
     * the last one, so narrowing the column count can never orphan content.
     *
     * @param  array<string, mixed>  $block
     * @param  array<string, mixed>  $context
     * @return array<int, string> HTML per column index
     */
    private function renderChildren(array $block, Contracts\Widget $definition, array $context): array
    {
        $settings = $block['settings'] ?? [];
        $defaults = $definition->defaults();

        $columns = max(1, min(4, (int) ($settings['columns'] ?? $defaults['columns'] ?? 1)));

        $byColumn = [];

        foreach (is_array($block['children'] ?? null) ? $block['children'] : [] as $child) {
            if (! is_array($child)) {
                continue;
            }

            $column = (int) ($child['column'] ?? 0);
            $column = min(max($column, 0), $columns - 1);

            $byColumn[$column][] = $child;
        }

        $rendered = [];

        for ($column = 0; $column < $columns; $column++) {
            $rendered[$column] = $this->renderBlocks($byColumn[$column] ?? [], $context);
        }

        return $rendered;
    }

    /**
     * Render a single widget with the given settings merged over its defaults.
     *
     * Record detail templates pass the current record through $context so the
     * dynamic widgets can render it. The builder canvas and preview endpoint
     * additionally set authoring to true, so widgets that would render nothing
     * on a live page (no cover image, no related items) still show a labelled
     * placeholder the editor can select and reorder.
     *
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>  $context
     */
    public function renderBlock(string $widget, array $settings = [], array $context = []): string
    {
        $definition = $this->registry->get($widget);

        if ($definition === null) {
            return '';
        }

        $resolved = $this->resolveSettings($definition->defaults(), $settings);

        return $this->views->make($definition->view(), [
            'settings' => $resolved,
            'record' => $context['record'] ?? null,
            'authoring' => (bool) ($context['authoring'] ?? false),
            // Container widgets render their already-rendered children here,
            // keyed by column; every other widget ignores the variable.
            'children' => $context['children'] ?? [],
            // Some widgets embed forms (e.g. the enquiry form); supply an error
            // bag so they render outside a request, such as in tests.
            'errors' => Session::has('errors') ? Session::get('errors') : new ViewErrorBag,
        ])->render();
    }

    /**
     * Merge incoming settings over defaults, keeping only known keys.
     *
     * @param  array<string, mixed>  $defaults
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function resolveSettings(array $defaults, array $settings): array
    {
        $clean = [];

        foreach ($defaults as $key => $default) {
            $value = $settings[$key] ?? $default;

            // Repeaters are lists of rows; keep only rows that are arrays.
            if (is_array($default) && $this->isList($default)) {
                $clean[$key] = array_values(array_filter(
                    is_array($value) ? $value : [],
                    'is_array',
                ));

                continue;
            }

            $clean[$key] = $value;
        }

        return $clean;
    }

    /**
     * Determine whether an array is a list (sequential integer keys).
     *
     * @param  array<array-key, mixed>  $array
     */
    private function isList(array $array): bool
    {
        if ($array === []) {
            return true;
        }

        return array_keys($array) === range(0, count($array) - 1);
    }
}
