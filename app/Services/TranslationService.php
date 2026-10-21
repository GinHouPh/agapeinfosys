<?php

namespace App\Services;

use App\Builder\Contracts\Widget;
use App\Builder\WidgetRegistry;
use App\Models\Concerns\HasTranslations;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;

/**
 * Works out what can be translated on a record, and saves what has been.
 */
class TranslationService
{
    public function __construct(private readonly WidgetRegistry $registry) {}

    /**
     * The fields of a record that can be translated, addressed by their key.
     *
     * A plain attribute is addressed by its name. A setting inside a
     * builder schema is addressed as "block.<id>.<setting>", which survives
     * the section being moved.
     *
     * @return array<string, string>
     */
    public function fields(Model $record): array
    {
        $fields = [];

        foreach (['title', 'meta_description', 'excerpt', 'summary'] as $attribute) {
            $value = $record->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                $fields[$attribute] = ucfirst(str_replace('_', ' ', $attribute));
            }
        }

        foreach ($this->blocks($record) as $block) {
            $widget = $this->registry->get($block['widget'] ?? '');

            if ($widget === null) {
                continue;
            }

            $settings = is_array($block['settings'] ?? null) ? $block['settings'] : [];

            foreach ($widget->controls() as $control) {
                $key = $control['key'] ?? null;

                if (! is_string($key)) {
                    continue;
                }

                if (($control['type'] ?? 'text') === 'repeater') {
                    $fields += $this->repeaterFields($block['id'], $widget, $control, $settings[$key] ?? []);

                    continue;
                }

                if (! in_array($control['type'] ?? 'text', ['text', 'textarea'], true)) {
                    continue;
                }

                $value = $settings[$key] ?? null;

                if (! is_string($value) || $value === '') {
                    continue;
                }

                $fields['block.'.$block['id'].'.'.$key] = $widget->title().' — '.($control['label'] ?? $key);
            }
        }

        return $fields;
    }

    /**
     * The translatable fields of one repeater's rows.
     *
     * A row is addressed by its own id, so the translation follows the row
     * when the list is reordered.
     *
     * @param  array<string, mixed>  $rows
     * @return array<string, string>
     */
    private function repeaterFields(string $blockId, Widget $widget, array $control, mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $fields = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $rowId = $row['id'] ?? null;

            if (! is_string($rowId) || $rowId === '') {
                continue;
            }

            foreach (($control['fields'] ?? []) as $sub) {
                $subKey = $sub['key'] ?? null;

                if (! is_string($subKey) || ! in_array($sub['type'] ?? 'text', ['text', 'textarea'], true)) {
                    continue;
                }

                $value = $row[$subKey] ?? null;

                if (! is_string($value) || $value === '') {
                    continue;
                }

                $address = 'block.'.$blockId.'.'.$control['key'].'.'.$rowId.'.'.$subKey;

                $fields[$address] = $widget->title().' — '.($control['label'] ?? $control['key']).' · '.($sub['label'] ?? $subKey);
            }
        }

        return $fields;
    }

    /**
     * The value each field currently holds in a language.
     *
     * @param  array<string, string>  $fields
     * @return array<string, string>
     */
    public function values(Model $record, array $fields, ?string $locale = null): array
    {
        $map = $record instanceof Model && method_exists($record, 'translationMap')
            ? $record->translationMap($locale)
            : [];

        $values = [];

        foreach ($fields as $address => $label) {
            $values[$address] = $map[$address] ?? $this->rawValue($record, $address);
        }

        return $values;
    }

    /**
     * Save the translations of a record.
     *
     * @param  array<string, string>  $values
     */
    public function save(Model $record, int $localeId, array $values): void
    {
        foreach ($values as $address => $value) {
            $value = is_string($value) ? trim($value) : '';

            if ($value === '') {
                Translation::query()
                    ->where('locale_id', $localeId)
                    ->where('translatable_type', $record->getMorphClass())
                    ->where('translatable_id', $record->getKey())
                    ->where('field', $address)
                    ->delete();

                continue;
            }

            Translation::query()->updateOrCreate([
                'locale_id' => $localeId,
                'translatable_type' => $record->getMorphClass(),
                'translatable_id' => $record->getKey(),
                'field' => $address,
            ], ['value' => $value]);
        }
    }

    /**
     * The record's own value for a field, before any translation.
     */
    private function rawValue(Model $record, string $address): string
    {
        if (! str_starts_with($address, 'block.')) {
            $value = $record->getAttribute($address);

            return is_string($value) ? $value : '';
        }

        $parts = explode('.', $address);

        // block.{id}.{repeater}.{row}.{field}
        if (count($parts) === 5) {
            [, $blockId, $repeaterKey, $rowId, $fieldKey] = $parts;

            foreach ($this->blocks($record) as $block) {
                if (($block['id'] ?? null) !== $blockId) {
                    continue;
                }

                foreach ((array) ($block['settings'][$repeaterKey] ?? []) as $row) {
                    if (! is_array($row) || ($row['id'] ?? null) !== $rowId) {
                        continue;
                    }

                    $value = $row[$fieldKey] ?? null;

                    return is_string($value) ? $value : '';
                }
            }

            return '';
        }

        if (count($parts) !== 3) {
            return '';
        }

        foreach ($this->blocks($record) as $block) {
            if (($block['id'] ?? null) !== $parts[1]) {
                continue;
            }

            $value = $block['settings'][$parts[2]] ?? null;

            return is_string($value) ? $value : '';
        }

        return '';
    }

    /**
     * The builder blocks of a record.
     *
     * @return array<int, array<string, mixed>>
     */
    private function blocks(Model $record): array
    {
        if (! in_array(HasTranslations::class, class_uses_recursive($record))) {
            return [];
        }

        $schema = $record->schema ?? [];
        $blocks = is_array($schema['blocks'] ?? null) ? $schema['blocks'] : [];

        return array_values(array_filter($blocks, 'is_array'));
    }
}
