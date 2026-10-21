<?php

namespace App\Models\Concerns;

use App\Models\Translation;
use App\Services\LocaleService;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Gives a record its own translations.
 *
 * A translation is one field of one record in one language. The field is
 * either a plain attribute ("title") or a setting inside a builder schema,
 * addressed as "block.<id>.<setting>" so it survives a section being
 * reordered.
 */
trait HasTranslations
{
    /**
     * Every translation of this record.
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * The value of a field in a language, falling back when there is none.
     */
    public function translated(string $field, ?string $locale = null): ?string
    {
        $translated = $this->translationMap($locale);

        $value = $translated[$field] ?? null;

        if (is_string($value) && $value !== '') {
            return $value;
        }

        $raw = $this->getAttribute($field);

        return is_string($raw) && $raw !== '' ? $raw : null;
    }

    /**
     * The translations of this record for a language, keyed by field.
     *
     * The requested language is tried first, then the default language, so
     * a half-translated record still reads as a whole one.
     *
     * @return array<string, string>
     */
    public function translationMap(?string $locale = null): array
    {
        $locale ??= app(LocaleService::class)->currentCode();

        $records = $this->translations()
            ->with('locale')
            ->when($locale !== null, fn ($query) => $query->whereHas(
                'locale',
                fn ($builder) => $builder->where('code', $locale),
            ))
            ->get();

        $map = [];

        foreach ($records as $record) {
            $map[$record->field] = (string) $record->value;
        }

        return $map;
    }

    /**
     * The builder schema with its translated settings applied.
     *
     * @return array<string, mixed>
     */
    public function translatedSchema(?string $locale = null): array
    {
        $schema = $this->schema ?? [];

        if (! is_array($schema)) {
            return [];
        }

        $translations = $this->translationMap($locale);

        if ($translations === []) {
            return $schema;
        }

        $schema['blocks'] = $this->applyTranslations(
            is_array($schema['blocks'] ?? null) ? $schema['blocks'] : [],
            $translations,
        );

        return $schema;
    }

    /**
     * The blocks of a builder page in the visitor's language.
     *
     * @return array<int, array<string, mixed>>
     */
    public function localisedBlocks(): array
    {
        $schema = $this->translatedSchema();

        $blocks = is_array($schema['blocks'] ?? null) ? $schema['blocks'] : [];

        return array_values(array_filter($blocks, 'is_array'));
    }

    /**
     * Walk the block tree, swapping in the translated settings.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, string>  $translations
     * @return array<int, array<string, mixed>>
     */
    private function applyTranslations(array $blocks, array $translations): array
    {
        foreach ($blocks as $index => $block) {
            if (! is_array($block)) {
                continue;
            }

            $id = is_string($block['id'] ?? null) ? $block['id'] : '';

            if ($id !== '' && is_array($block['settings'] ?? null)) {
                $block['settings'] = $this->applySettings($block['settings'], $id, $translations);
            }

            if (is_array($block['children'] ?? null)) {
                $block['children'] = $this->applyTranslations($block['children'], $translations);
            }

            $blocks[$index] = $block;
        }

        return $blocks;
    }

    /**
     * Swap in the translated values of one block's settings.
     *
     * @param  array<string, mixed>  $settings
     * @param  array<string, string>  $translations
     * @return array<string, mixed>
     */
    private function applySettings(array $settings, string $id, array $translations): array
    {
        foreach ($settings as $key => $value) {
            $address = 'block.'.$id.'.'.$key;

            if (is_array($value) && array_is_list($value)) {
                $settings[$key] = $this->applyRows($value, $id, $key, $translations);

                continue;
            }

            if (is_string($value) && isset($translations[$address]) && $translations[$address] !== '') {
                $settings[$key] = $translations[$address];

                continue;
            }

            if (is_array($value) && ! array_is_list($value)) {
                $settings[$key] = $this->applySettings($value, $id, $translations);
            }
        }

        return $settings;
    }

    /**
     * Swap in the translated values of one repeater's rows.
     *
     * A row is addressed by its own id, not by its position, so moving a
     * row up or down does not shuffle its translation onto its neighbour.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, string>  $translations
     * @return array<int, array<string, mixed>>
     */
    private function applyRows(array $rows, string $blockId, string $repeaterKey, array $translations): array
    {
        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $rowId = $row['id'] ?? null;

            if (! is_string($rowId) || $rowId === '') {
                continue;
            }

            foreach ($row as $fieldKey => $fieldValue) {
                $address = 'block.'.$blockId.'.'.$repeaterKey.'.'.$rowId.'.'.$fieldKey;

                if (is_string($fieldValue) && isset($translations[$address]) && $translations[$address] !== '') {
                    $rows[$index][$fieldKey] = $translations[$address];
                }
            }
        }

        return $rows;
    }
}
