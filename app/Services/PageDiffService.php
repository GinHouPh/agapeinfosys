<?php

namespace App\Services;

/**
 * Compares two page layouts.
 *
 * The builder stores a tree of blocks, each with a bag of settings. A
 * diff flattens both versions into one line per setting and reports what
 * moved, so an author can see what changed without reading JSON.
 */
class PageDiffService
{
    /**
     * The differences between two schemas.
     *
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     * @return array<int, array{type: string, widget: string, setting: string, before: mixed, after: mixed}>
     */
    public function compare(?array $before, ?array $after): array
    {
        $old = $this->flatten($before ?? []);
        $new = $this->flatten($after ?? []);

        $changes = [];

        foreach ($new as $id => $block) {
            if (! isset($old[$id])) {
                $changes[] = [
                    'type' => 'added',
                    'widget' => $block['widget'],
                    'setting' => 'the whole section',
                    'before' => null,
                    'after' => 'Added',
                ];

                continue;
            }

            foreach ($block['settings'] as $key => $value) {
                $previous = $old[$id]['settings'][$key] ?? null;

                if ($previous === $value) {
                    continue;
                }

                $changes[] = [
                    'type' => 'changed',
                    'widget' => $block['widget'],
                    'setting' => $key,
                    'before' => $this->readable($previous),
                    'after' => $this->readable($value),
                ];
            }

            foreach (array_keys($old[$id]['settings']) as $key) {
                if (! array_key_exists($key, $block['settings'])) {
                    $changes[] = [
                        'type' => 'removed',
                        'widget' => $old[$id]['widget'],
                        'setting' => $key,
                        'before' => $this->readable($old[$id]['settings'][$key]),
                        'after' => null,
                    ];
                }
            }
        }

        foreach ($old as $id => $block) {
            if (! isset($new[$id])) {
                $changes[] = [
                    'type' => 'removed',
                    'widget' => $block['widget'],
                    'setting' => 'the whole section',
                    'before' => 'Removed',
                    'after' => null,
                ];
            }
        }

        return $changes;
    }

    /**
     * Flatten a schema into one entry per block, keyed by its id.
     *
     * @param  array<string, mixed>  $schema
     * @return array<string, array{widget: string, settings: array<string, mixed>}>
     */
    private function flatten(array $schema): array
    {
        $flat = [];

        $this->walk(is_array($schema['blocks'] ?? null) ? $schema['blocks'] : [], $flat);

        return $flat;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, array{widget: string, settings: array<string, mixed>}>  $flat
     */
    private function walk(array $blocks, array &$flat): void
    {
        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            $id = $block['id'] ?? null;

            if (is_string($id) && $id !== '') {
                $flat[$id] = [
                    'widget' => is_string($block['widget'] ?? null) ? $block['widget'] : 'block',
                    'settings' => $this->flattenSettings(
                        is_array($block['settings'] ?? null) ? $block['settings'] : [],
                    ),
                ];
            }

            if (is_array($block['children'] ?? null)) {
                $this->walk($block['children'], $flat);
            }
        }
    }

    /**
     * Flatten nested settings into dotted paths.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function flattenSettings(array $settings, string $prefix = ''): array
    {
        $flat = [];

        foreach ($settings as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value) && ! array_is_list($value)) {
                $flat += $this->flattenSettings($value, $path);

                continue;
            }

            $flat[$path] = $value;
        }

        return $flat;
    }

    /**
     * A value worth showing in a diff.
     */
    private function readable(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'yes' : 'no';
        }

        if (is_array($value)) {
            return implode(', ', array_map(fn ($item): string => $this->readable($item) === null ? '' : (string) $this->readable($item), $value));
        }

        if (is_string($value)) {
            return $value === '' ? '(empty)' : $value;
        }

        return $value;
    }
}
