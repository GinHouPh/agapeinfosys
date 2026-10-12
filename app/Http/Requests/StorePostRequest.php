<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
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
        $postId = $this->route('post')?->getKey();

        return [
            'title' => ['required', 'string', 'max:160'],
            'slug' => [
                'nullable', 'string', 'max:180', 'alpha_dash',
                Rule::unique('posts', 'slug')->ignore($postId),
            ],
            'category' => ['nullable', 'string', 'max:80'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['nullable', 'string', 'max:60000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:4096'],
            'reading_time' => ['nullable', 'string', 'max:40'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_published' => ['boolean'],
        ];
    }

    /**
     * Coerce toggles and blank numeric fields before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->has('is_published') ? $this->boolean('is_published') : true,
            'sort_order' => $this->input('sort_order') === '' ? 0 : $this->input('sort_order'),
            'published_at' => $this->input('published_at') === '' ? null : $this->input('published_at'),
        ]);
    }
}
