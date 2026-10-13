<?php

namespace App\Builder\Contracts;

/**
 * One widget the visual page builder can place and render.
 *
 * Mirrors the Elementor/ElementsKit widget contract: a declarative control
 * schema describes every editable field, and a Blade view renders it.
 */
interface Widget
{
    /**
     * Machine name used in the page schema (e.g. "hero").
     */
    public function name(): string;

    /**
     * Display name shown in the builder palette.
     */
    public function title(): string;

    /**
     * Palette grouping (e.g. "Layout", "Content", "Dynamic").
     */
    public function category(): string;

    /**
     * Short one-line description shown in the palette.
     */
    public function description(): string;

    /**
     * Inline SVG path (24x24 viewBox) used as the palette icon.
     */
    public function icon(): string;

    /**
     * Declarative control schema. Each control:
     * [
     *     'key'       => string,
     *     'label'     => string,
     *     'type'      => text|textarea|number|select|toggle|image|url|repeater,
     *     'default'   => mixed,
     *     'options'   => [value => label],          // select
     *     'fields'    => [control, ...],            // repeater row fields
     *     'condition' => [key => expected, ...],    // show only when matching
     *     'help'      => string,
     * ]
     *
     * @return array<int, array<string, mixed>>
     */
    public function controls(): array;

    /**
     * Default settings derived from the control schema.
     *
     * @return array<string, mixed>
     */
    public function defaults(): array;

    /**
     * Whether this widget can hold other blocks (a section, a column band...).
     *
     * Containers carry an ordered `children` list in the schema; every other
     * widget has its children stripped by the sanitizer.
     */
    public function isContainer(): bool;

    /**
     * Blade view rendering the widget's public markup.
     */
    public function view(): string;
}
