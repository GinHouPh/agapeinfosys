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
