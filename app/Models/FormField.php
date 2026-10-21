<?php

namespace App\Models;

use App\Enums\FormFieldType;
use Database\Factories\FormFieldFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * One question inside a builder-managed form.
 */
class FormField extends Model
{
    /** @use HasFactory<FormFieldFactory> */
    use HasFactory;

    protected $fillable = [
        'form_id',
        'label',
        'name',
        'type',
        'group',
        'options',
        'placeholder',
        'help_text',
        'validation',
        'condition',
        'width',
        'file_settings',
        'is_required',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => FormFieldType::class,
            'options' => 'array',
            'validation' => 'array',
            'condition' => 'array',
            'file_settings' => 'array',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * The extra validation rules the author set on this field.
     *
     * @return array<int, string>
     */
    public function validationRules(): array
    {
        $rules = $this->validation ?? [];

        if (! is_array($rules)) {
            return [];
        }

        return array_values(array_filter($rules, 'is_string'));
    }

    /**
     * The file types this field accepts.
     *
     * @return list<string>
     */
    public function acceptedMimes(): array
    {
        $mimes = $this->file_settings['mimes'] ?? [];

        return is_array($mimes) ? array_values(array_filter($mimes, 'is_string')) : [];
    }

    /**
     * The largest upload this field accepts, in kilobytes.
     */
    public function maxUploadKilobytes(): int
    {
        $max = $this->file_settings['max_kilobytes'] ?? 5120;

        return is_numeric($max) ? (int) $max : 5120;
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Derive a machine name from the label when none is supplied.
     */
    public static function nameFromLabel(string $label): string
    {
        $name = Str::slug($label, '_');

        return $name !== '' ? $name : 'field_'.Str::lower(Str::random(6));
    }

    /**
     * Group consecutive fields for the sectioned public renderer.
     *
     * @return array<int, array{group: string|null, fields: Collection<int, FormField>}>
     */
    public static function groupForRenderer(iterable $fields): array
    {
        $groups = [];

        foreach ($fields as $field) {
            $last = array_key_last($groups);

            if ($last === null || $groups[$last]['group'] !== $field->group) {
                $groups[] = ['group' => $field->group, 'fields' => collect()];
            }

            $groups[array_key_last($groups)]['fields']->push($field);
        }

        return $groups;
    }
}
