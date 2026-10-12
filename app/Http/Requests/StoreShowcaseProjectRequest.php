<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShowcaseProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $projectId = $this->route('showcase_project')?->getKey();

        return [
            'title' => ['required', 'string', 'max:160'],
            'slug' => [
                'nullable', 'string', 'max:180', 'alpha_dash',
                Rule::unique('showcase_projects', 'slug')->ignore($projectId),
            ],
            'industry' => ['required', 'string', 'max:80'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'tagline' => ['nullable', 'string', 'max:240'],
            'description' => ['nullable', 'string', 'max:4000'],
            'poster' => ['nullable', 'image', 'max:4096'],
            'stack' => ['nullable', 'string', 'max:300'],
            'expected_launch' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Coerce unchecked toggles and empty numeric fields before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->input('sort_order') === '' ? 0 : $this->input('sort_order'),
            'expected_launch' => $this->input('expected_launch') === '' ? null : $this->input('expected_launch'),
        ]);
    }
}
