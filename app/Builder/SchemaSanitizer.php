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

            if (($control['responsive'] ?? false) === true) {
                foreach (Device::overrides($key) as $overrideKey) {
                    $value = $incoming[$overrideKey] ?? null;

                    if (! is_scalar($value)) {
                        continue;
                    }

                    if (is_string($value) && trim($value) === '') {
                        continue;
                    }

                    $coerced = $this->coerce($control, $value);

                    // An override is kept only if it survives coercion
                    // unchanged. Anything else is a value nobody chose — a
                    // blank field, an option this control does not offer — and
                    // pinning it would break the inheritance the panel
                    // promised, so the key stays absent and the tier falls
                    // back to desktop. The base key gets no such escape
                    // because it has to hold something.
                    if (! is_scalar($coerced) || (string) $coerced !== (string) $value) {
                        continue;
                    }

                    $clean[$overrideKey] = $coerced;
                }
            }
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
            'color' => $this->coerceColor($control, $value),
            'url' => $this->coerceUrl($value),
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
     * A colour is the name of a design token, never a colour value.
     *
     * Keeping the name rather than a hex is what lets the light theme repaint
     * the widget and a rebrand reach it at all: the view binds the name to the
     * matching `--color-*` property, so the value resolves at render time.
     * Anything that is not a known token binds to nothing and would render as
     * whatever the element inherits, so it falls back to the default instead
     * of being stored as a value nobody picked.
     *
     * @param  array<string, mixed>  $control
     */
    private function coerceColor(array $control, mixed $value): string
    {
        if (DesignTokens::isColor($value)) {
            return $value;
        }

        return DesignTokens::isColor($control['default'] ?? null)
            ? (string) $control['default']
            : DesignTokens::FALLBACK;
    }

    /**
     * Drop a link whose scheme a browser cannot be trusted to navigate to.
     *
     * URL settings are echoed straight into href attributes on public pages,
     * so a javascript: or data: scheme stored here would be a stored XSS that
     * fires on click. Control characters are stripped before the scheme is
     * read, because the URL parser discards tabs and newlines first — without
     * that, "java\nscript:alert(1)" would sail past a prefix check.
     *
     * Schemeless values (a site path, an anchor, a query) carry no scheme to
     * misuse, so they are kept exactly as authored.
     */
    private function coerceUrl(mixed $value): string
    {
        $value = is_scalar($value) ? (string) $value : '';
        $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value) ?? '';
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (preg_match('/^([a-z][a-z0-9+.-]*):/i', $value, $match) !== 1) {
            return $value;
        }

        $scheme = strtolower($match[1]);

        return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true) ? $value : '';
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
