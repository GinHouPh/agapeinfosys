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

        return $validated;
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
