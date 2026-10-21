<?php

namespace App\Services;

use App\Models\CollectionRecord;
use App\Models\CollectionType;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationRule;

/**
 * Validates and shapes the data of a collection record.
 */
class CollectionService
{
    /**
     * The validation rules for a record of a type.
     *
     * @return array<string, ValidationRule|array<int, mixed>|string>
     */
    public function validationRules(CollectionType $type, ?CollectionRecord $record = null): array
    {
        $rules = [];

        foreach ($type->fields as $field) {
            $key = 'data.'.$field->name;
            $base = $field->is_required ? 'required' : 'nullable';

            if ($field->type === 'select' || $field->type === 'radio') {
                $options = $field->options ?? [];

                $rules[$key] = array_filter(array_merge([
                    $base,
                    Rule::in(is_array($options) ? $options : []),
                ], $field->validationRules()));

                continue;
            }

            $rules[$key] = array_filter(array_merge([
                $base,
                match ($field->type) {
                    'email' => 'email',
                    'number' => 'numeric',
                    'date' => 'date',
                    'url' => 'url',
                    'checkbox' => 'array',
                    default => 'string',
                },
                'max:5000',
            ], $field->validationRules()));
        }

        return $rules;
    }

    /**
     * Keep only the fields the type declares, and coerce their values.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function sanitise(CollectionType $type, array $data): array
    {
        $clean = [];

        foreach ($type->fields as $field) {
            if (! Arr::has($data, $field->name)) {
                continue;
            }

            $value = $data[$field->name];

            $clean[$field->name] = match ($field->type) {
                'number' => is_numeric($value) ? (float) $value : null,
                'checkbox' => is_array($value) ? array_values(array_filter($value, 'is_scalar')) : [],
                'text', 'textarea', 'email', 'url', 'date', 'select', 'radio' => is_scalar($value) ? (string) $value : null,
                default => $value,
            };
        }

        return $clean;
    }
}
