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
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
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
