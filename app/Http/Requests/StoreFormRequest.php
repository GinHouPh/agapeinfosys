<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreFormRequest extends FormRequest
{
    /**
     * Only signed-in admins manage forms.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $formId = $this->route('form')?->id;

        return [
            'title' => ['required', 'string', 'max:160'],
            'slug' => [
                'nullable', 'string', 'max:180', 'alpha_dash',
                Rule::unique('forms', 'slug')->ignore($formId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'submit_label' => ['required', 'string', 'max:80'],
            'success_message' => ['nullable', 'string', 'max:400'],
            'reference_prefix' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9-]+$/'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Derive the slug from the title when left blank.
     *
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        if (($validated['slug'] ?? '') === '' || $validated['slug'] === null) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['is_active'] = $this->boolean(
            'is_active',
            $this->route('form')?->is_active ?? true,
        );

        return $validated;
    }
}
