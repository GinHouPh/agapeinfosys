<?php

namespace App\Http\Requests;

use App\Enums\FormFieldType;
use App\Models\FormField;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreFormFieldRequest extends FormRequest
{
    /**
     * Only signed-in admins manage questions.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * Options arrive as a newline-separated string from the builder textarea.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $form = $this->route('form');
        $fieldId = $this->route('field')?->id;

        return [
            'label' => ['required', 'string', 'max:190'],
            'name' => [
                'nullable', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/',
                Rule::unique('form_fields', 'name')->where('form_id', $form->id)->ignore($fieldId),
            ],
            'type' => ['required', Rule::enum(FormFieldType::class)],
            'group' => ['nullable', 'string', 'max:120'],
            'options' => ['nullable', 'string', 'max:4000'],
            'placeholder' => ['nullable', 'string', 'max:190'],
            'help_text' => ['nullable', 'string', 'max:240'],
            'width' => ['nullable', Rule::in(['full', 'half'])],
            'validation' => ['nullable', 'string', 'max:400'],
            'condition_field' => ['nullable', 'string', 'max:100'],
            'condition_operator' => ['nullable', Rule::in(['equals', 'not_equals', 'contains', 'not_contains', 'is_filled', 'is_empty'])],
            'condition_value' => ['nullable', 'string', 'max:190'],
            'file_mimes' => ['nullable', 'string', 'max:400'],
            'file_max_kilobytes' => ['nullable', 'integer', 'min:1', 'max:204800'],
            'is_required' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Split the options textarea, null it for types that do not render
     * choices, and default the machine name from the label.
     *
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        $type = FormFieldType::from($validated['type']);

        if ($type->hasOptions()) {
            $options = array_values(array_filter(
                array_map('trim', preg_split('/\R/', (string) $validated['options'])),
                fn (string $value): bool => $value !== '',
            ));

            if ($options === []) {
                throw ValidationException::withMessages([
                    'options' => 'Add at least one option, one per line.',
                ]);
            }

            $validated['options'] = $options;
        } else {
            $validated['options'] = null;
        }

        if (Arr::get($validated, 'name') === '' || Arr::get($validated, 'name') === null) {
            $validated['name'] = $this->deriveUniqueName($validated['label']);
        }

        $validated['is_required'] = $this->boolean(
            'is_required',
            $this->route('field')?->is_required ?? false,
        );

        $validated['width'] = $validated['width'] ?? 'full';

        $validated['validation'] = $this->splitRules($validated['validation'] ?? null);

        $validated['condition'] = $this->buildCondition($validated);

        $validated['file_settings'] = $this->buildFileSettings($validated, $type);

        unset(
            $validated['condition_field'],
            $validated['condition_operator'],
            $validated['condition_value'],
            $validated['file_mimes'],
            $validated['file_max_kilobytes'],
        );

        return $validated;
    }

    /**
     * Split the extra rules textarea, one rule per line.
     *
     * @return array<int, string>
     */
    private function splitRules(?string $rules): array
    {
        if ($rules === null || trim($rules) === '') {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', preg_split('/\R/', $rules)),
            fn (string $rule): bool => $rule !== '',
        ));
    }

    /**
     * Build the condition from the three flat inputs.
     *
     * @param  array<string, mixed>  $validated
     * @return array<int, array<string, mixed>>
     */
    private function buildCondition(array $validated): array
    {
        $field = $validated['condition_field'] ?? null;
        $operator = $validated['condition_operator'] ?? null;

        if (! is_string($field) || $field === '' || ! is_string($operator) || $operator === '') {
            return [];
        }

        return [[
            'field' => $field,
            'operator' => $operator,
            'value' => $validated['condition_value'] ?? '',
        ]];
    }

    /**
     * Build the upload rules for a file field.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>|null
     */
    private function buildFileSettings(array $validated, FormFieldType $type): ?array
    {
        if (! $type->isFile()) {
            return null;
        }

        $mimes = array_values(array_filter(
            array_map('trim', explode(',', (string) ($validated['file_mimes'] ?? ''))),
            fn (string $mime): bool => $mime !== '',
        ));

        return [
            'mimes' => $mimes,
            'max_kilobytes' => (int) ($validated['file_max_kilobytes'] ?? 5120),
        ];
    }

    /**
     * Auto-generated keys are suffixed when the label collides with an
     * existing question, so two questions may safely share a label.
     */
    private function deriveUniqueName(string $label): string
    {
        $base = FormField::nameFromLabel($label);
        $formId = $this->route('form')->id;
        $fieldId = $this->route('field')?->id;

        $name = $base;
        $suffix = 1;

        while (FormField::query()
            ->where('form_id', $formId)
            ->where('name', $name)
            ->when($fieldId !== null, fn ($query) => $query->where('id', '!=', $fieldId))
            ->exists()) {
            $name = $base.'_'.++$suffix;
        }

        return $name;
    }
}
