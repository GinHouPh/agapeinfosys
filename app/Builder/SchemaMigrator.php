<?php

namespace App\Builder;

use App\Builder\Contracts\Widget;

/**
 * Carries a stored block forward to the shape its widget has now.
 *
 * A page records the widget version of every block it holds. A widget that
 * changes shape declares how to move a block from one version to the next;
 * this applies those steps until the block is current.
 *
 * Migration happens when the page is read, so an old page renders with the
 * widget as it is today without anyone having to run anything. The
 * `builder:migrate-pages` command bakes the result in.
 */
class SchemaMigrator
{
    public function __construct(private readonly WidgetRegistry $registry) {}

    /**
     * Whether a block needs migrating.
     *
     * @param  array<string, mixed>  $block
     */
    public function needsMigration(array $block): bool
    {
        $widget = $this->widgetFor($block);

        if ($widget === null) {
            return false;
        }

        return $this->storedVersion($block) < $widget->version();
    }

    /**
     * Move a block forward to the widget's current version.
     *
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    public function migrate(array $block): array
    {
        $widget = $this->widgetFor($block);

        if ($widget === null) {
            return $block;
        }

        $version = $this->storedVersion($block);
        $target = $widget->version();

        if ($version >= $target) {
            return $block;
        }

        $settings = is_array($block['settings'] ?? null) ? $block['settings'] : [];
        $migrations = $widget->migrations();

        for ($step = $version; $step < $target; $step++) {
            $migration = $migrations[$step] ?? null;

            if (! is_array($migration)) {
                continue;
            }

            $settings = $this->applyMigration($settings, $migration);
        }

        $block['settings'] = $settings;
        $block['version'] = $target;

        return $block;
    }

    /**
     * Move a whole block tree forward.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public function migrateTree(array $blocks): array
    {
        foreach ($blocks as $index => $block) {
            if (! is_array($block)) {
                continue;
            }

            $blocks[$index] = $this->migrate($block);

            if (is_array($block['children'] ?? null)) {
                $blocks[$index]['children'] = $this->migrateTree($block['children']);
            }
        }

        return $blocks;
    }

    /**
     * Apply one migration step to a block's settings.
     *
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>  $migration
     * @return array<string, mixed>
     */
    private function applyMigration(array $settings, array $migration): array
    {
        foreach (($migration['rename'] ?? []) as $from => $to) {
            if (! is_string($from) || ! is_string($to) || ! array_key_exists($from, $settings)) {
                continue;
            }

            $value = $settings[$from];
            unset($settings[$from]);

            if (! array_key_exists($to, $settings)) {
                $settings[$to] = $value;
            }
        }

        foreach (($migration['drop'] ?? []) as $key) {
            if (is_string($key)) {
                unset($settings[$key]);
            }
        }

        foreach (($migration['default'] ?? []) as $key => $value) {
            if (is_string($key) && ! array_key_exists($key, $settings)) {
                $settings[$key] = $value;
            }
        }

        foreach (($migration['row_id'] ?? []) as $key) {
            if (! is_string($key) || ! is_array($settings[$key] ?? null)) {
                continue;
            }

            foreach ($settings[$key] as $index => $row) {
                if (! is_array($row) || ! empty($row['id'])) {
                    continue;
                }

                $settings[$key][$index]['id'] = WidgetRegistry::newBlockId();
            }
        }

        return $settings;
    }

    /**
     * The widget a block belongs to.
     *
     * @param  array<string, mixed>  $block
     */
    private function widgetFor(array $block): ?Widget
    {
        $name = $block['widget'] ?? null;

        if (! is_string($name) || $name === '') {
            return null;
        }

        return $this->registry->get($name);
    }

    /**
     * The version a block was stored at. A block with no version predates
     * versioning and is treated as the first version.
     *
     * @param  array<string, mixed>  $block
     */
    private function storedVersion(array $block): int
    {
        $version = $block['version'] ?? null;

        return is_numeric($version) ? (int) $version : 1;
    }
}
