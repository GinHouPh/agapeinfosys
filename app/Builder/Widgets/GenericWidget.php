<?php

namespace App\Builder\Widgets;

use App\Builder\Contracts\Widget;

/**
 * Config-driven base for simple widgets.
 *
 * A widget declares a single static config array; this base implements the
 * Widget contract from it, keeping each widget file small and readable.
 */
abstract class GenericWidget implements Widget
{
    /**
     * @return array{
     *     name: string,
     *     title: string,
     *     category: string,
     *     description: string,
     *     icon: string,
     *     view: string,
     *     controls: array<int, array<string, mixed>>,
     *     section?: bool,
     *     container?: bool,
     * }
     */
    abstract protected static function config(): array;

    public function name(): string
    {
        return static::config()['name'];
    }

    public function title(): string
    {
        return static::config()['title'];
    }

    public function category(): string
    {
        return static::config()['category'];
    }

    public function description(): string
    {
        return static::config()['description'];
    }

    public function icon(): string
    {
        return static::config()['icon'];
    }

    public function view(): string
    {
        return static::config()['view'];
    }

    /**
     * Whether the widget holds other blocks. Declared per widget in config.
     */
    public function isContainer(): bool
    {
        return (bool) (static::config()['container'] ?? false);
    }

    /**
     * The version of this widget's settings shape.
     *
     * A page records the version of every block it holds. When a widget
     * changes shape — a setting is renamed, a control is removed — the
     * page's blocks are migrated forward, so an old page keeps rendering
     * with the widget as it is now.
     */
    public function version(): int
    {
        return (int) (static::config()['version'] ?? 1);
    }

    /**
     * The migrations that carry a block from one version to the next.
     *
     * Keyed by the version being migrated FROM. Each entry is a list of
     * operations:
     *
     * - `rename`  — a map of old setting key => new setting key.
     * - `drop`    — setting keys to remove.
     * - `default` — a map of setting key => value to set when it is absent.
     *
     * @return array<int, array<string, mixed>>
     */
    public function migrations(): array
    {
        $migrations = static::config()['migrations'] ?? [];

        return is_array($migrations) ? $migrations : [];
    }

    /**
     * Controls, with the shared section controls appended when requested.
     *
     * @return array<int, array<string, mixed>>
     */
    public function controls(): array
    {
        $controls = static::config()['controls'];

        if (static::config()['section'] ?? true) {
            $controls = array_merge($controls, $this->sectionControls());
        }

        return $controls;
    }

    /**
     * Default settings derived from the control schema.
     *
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return $this->defaultsFromControls($this->controls());
    }

    /**
     * Collect defaults for a set of controls, recursing into repeaters.
     *
     * @param  array<int, array<string, mixed>>  $controls
     * @return array<string, mixed>
     */
    protected function defaultsFromControls(array $controls): array
    {
        $defaults = [];

        foreach ($controls as $control) {
            $key = $control['key'] ?? null;

            if ($key === null) {
                continue;
            }

            if (($control['type'] ?? 'text') === 'repeater') {
                $rowDefaults = $this->defaultsFromControls($control['fields'] ?? []);
                $defaults[$key] = $control['default'] ?? [$rowDefaults];

                continue;
            }

            $defaults[$key] = $control['default'] ?? '';
        }

        return $defaults;
    }

    /**
     * The shared section-level controls (background, spacing).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function sectionControls(): array
    {
        return [
            [
                'key' => 'background',
                'label' => 'Background',
                'type' => 'select',
                'default' => 'ink',
                'options' => [
                    'ink' => 'Dark (ink)',
                    'surface' => 'Surface',
                    'none' => 'Transparent',
                ],
            ],
            [
                'key' => 'spacing',
                'label' => 'Vertical spacing',
                'type' => 'select',
                'default' => 'default',
                'options' => [
                    'default' => 'Default',
                    'tight' => 'Tight',
                    'flush' => 'Tight, no top',
                    'none' => 'None',
                ],
            ],
        ];
    }
}
