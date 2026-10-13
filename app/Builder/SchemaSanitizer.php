<?php

namespace App\Builder;

/**
 * Whitelists a page's block schema against the widget control schemas.
 *
 * Everything the builder posts is untrusted: this keeps only blocks whose
 * widget actually exists, and, per block, only settings keys the widget
 * declares — coercing each value to its control's type. Containers keep a
 * bounded, recursively sanitised list of children. Unknown widgets and
 * stray keys are dropped rather than stored, so the JSON column stays clean
 * and a stored page can never smuggle data the renderer would not expect.
 */
class SchemaSanitizer
{
    /**
     * How deep the tree may nest: top level, inside a section, inside one more.
     *
     * Anything deeper is flattened on save, which keeps both the renderer and
     * the canvas from walking an unbounded structure.
     */
    public const MAX_DEPTH = 3;

    public function __construct(private readonly WidgetRegistry $registry) {}

    /**
     * Normalise an incoming schema to a well-formed nested block list.
     *
     * Blocks are validated recursively: a container keeps its children only
     * while there is nesting budget left, and every child records the column
     * it sits in. Widgets that cannot hold children have theirs discarded.
     *
     * @return array{blocks: array<int, array<string, mixed>>}
     */
    public function sanitize(mixed $schema): array
    {
        $blocks = is_array($schema) ? ($schema['blocks'] ?? null) : null;

        return ['blocks' => $this->sanitizeBlocks(is_array($blocks) ? $blocks : [], 0)];
    }

    /**
     * Validate one level of the tree and recurse into containers.
     *
     * @param  array<array-key, mixed>  $blocks
     * @return array<int, array<string, mixed>>
     */
    private function sanitizeBlocks(array $blocks, int $depth): array
    {
        $clean = [];

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            $name = $block['widget'] ?? null;

            if (! is_string($name) || $name === '') {
                continue;
            }

            $widget = $this->registry->get($name);

            if ($widget === null) {
                continue;
            }

            $entry = [
                'id' => $this->blockId($block['id'] ?? null),
                'widget' => $name,
                'settings' => $this->sanitizeSettings($widget, $block['settings'] ?? []),
            ];

            if ($depth > 0) {
                $entry['column'] = $this->blockColumn($block['column'] ?? 0);
            }

            if ($widget->isContainer()) {
                $entry['children'] = $depth + 1 < self::MAX_DEPTH
                    ? $this->sanitizeBlocks(
                        is_array($block['children'] ?? null) ? $block['children'] : [],
                        $depth + 1,
                    )
                    : [];
            }

            $clean[] = $entry;
        }

        return $clean;
    }

    /**
     * Clamp a child's column to a sane index; out-of-range falls to the first.
     */
    private function blockColumn(mixed $column): int
    {
        return is_numeric($column) ? min(max((int) $column, 0), 3) : 0;
    }

    /**
     * Reuse a client-supplied block id when it is a safe token, else mint one.
     */
    private function blockId(mixed $id): string
    {
        if (is_string($id) && preg_match('/^[a-z0-9_-]{1,40}$/', $id) === 1) {
            return $id;
        }

        return WidgetRegistry::newBlockId();
    }

    /**
     * Keep only declared control keys, coercing each to its control's type.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function sanitizeSettings(Contracts\Widget $widget, mixed $settings): array
    {
        $incoming = is_array($settings) ? $settings : [];
        $defaults = $widget->defaults();

        $clean = [];

        foreach ($widget->controls() as $control) {
            $key = $control['key'] ?? null;

            if (! is_string($key) || $key === '') {
                continue;
            }

            $clean[$key] = array_key_exists($key, $incoming)
                ? $this->coerce($control, $incoming[$key])
                : ($defaults[$key] ?? null);
        }

        return $clean;
    }

    /**
     * Cast one incoming value to the shape its control declares.
     *
     * @param  array<string, mixed>  $control
     */
    private function coerce(array $control, mixed $value): mixed
    {
        return match ($control['type'] ?? 'text') {
            'number' => is_numeric($value) ? $value + 0 : 0,
            'toggle' => filter_var($value, FILTER_VALIDATE_BOOL),
            'select' => $this->coerceSelect($control, $value),
            'repeater' => $this->coerceRepeater($control, $value),
            default => is_scalar($value) ? (string) $value : '',
        };
    }

    /**
     * A select value must be one of the declared options, else its default.
     *
     * @param  array<string, mixed>  $control
     */
    private function coerceSelect(array $control, mixed $value): string
    {
        $options = $control['options'] ?? [];

        $value = is_scalar($value) ? (string) $value : '';

        if (is_array($options) && in_array($value, array_map('strval', array_keys($options)), true)) {
            return $value;
        }

        return (string) ($control['default'] ?? (array_key_first($options) ?? ''));
    }

    /**
     * Keep only list rows, each row filtered to its declared sub-fields.
     *
     * @param  array<string, mixed>  $control
     * @return array<int, array<string, mixed>>
     */
    private function coerceRepeater(array $control, mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $fields = $control['fields'] ?? [];
        $rows = [];

        foreach (array_values($value) as $row) {
            if (! is_array($row)) {
                continue;
            }

            $clean = [];

            foreach ($fields as $field) {
                $key = $field['key'] ?? null;

                if (! is_string($key) || $key === '') {
                    continue;
                }

                $clean[$key] = array_key_exists($key, $row)
                    ? $this->coerce($field, $row[$key])
                    : ($field['default'] ?? null);
            }

            $rows[] = $clean;
        }

        return $rows;
    }
}
