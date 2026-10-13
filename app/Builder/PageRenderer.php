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
     */
    public function render(Page $page): string
    {
        return $this->renderBlocks($page->blocks());
    }

    /**
     * Render an ordered list of blocks, skipping unknown widgets.
     *
     * @param  array<int, array{id?: string, widget?: string, settings?: array<string, mixed>}>  $blocks
     */
    public function renderBlocks(array $blocks): string
    {
        $html = '';

        foreach ($blocks as $block) {
            $name = $block['widget'] ?? '';

            if (! is_string($name) || $name === '') {
                continue;
            }

            $html .= $this->renderBlock($name, $block['settings'] ?? []);
        }

        return $html;
    }

    /**
     * Render a single widget with the given settings merged over its defaults.
     *
     * @param  array<string, mixed>  $settings
     */
    public function renderBlock(string $widget, array $settings = []): string
    {
        $definition = $this->registry->get($widget);

        if ($definition === null) {
            return '';
        }

        $resolved = $this->resolveSettings($definition->defaults(), $settings);

        return $this->views->make($definition->view(), [
            'settings' => $resolved,
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
