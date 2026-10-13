<?php

namespace App\Http\Requests;

use App\Enums\PageStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
{
    /**
     * Only signed-in admins manage pages.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * The builder serialises the whole schema into a single hidden input, so
     * decode that JSON string before the array rules run against it.
     */
    protected function prepareForValidation(): void
    {
        $schema = $this->input('schema');

        if (! is_string($schema) || trim($schema) === '') {
            return;
        }

        $decoded = json_decode($schema, true);

        if (is_array($decoded)) {
            $this->merge(['schema' => $decoded]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $pageId = $this->route('page')?->id;

        return [
            'title' => ['required', 'string', 'max:160'],
            'slug' => [
                'nullable', 'string', 'max:180', 'alpha_dash',
                Rule::unique('pages', 'slug')->ignore($pageId),
            ],
            'status' => ['required', Rule::enum(PageStatus::class)],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'schema' => ['nullable', 'array'],
            'schema.blocks' => ['nullable', 'array'],
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

        return $validated;
    }
}
