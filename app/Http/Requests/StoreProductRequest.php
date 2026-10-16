<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
        $productId = $this->route('product')?->getKey();

        return [
            'name' => ['required', 'string', 'max:160'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'slug' => [
                'nullable', 'string', 'max:180', 'alpha_dash',
                Rule::unique('products', 'slug')->ignore($productId),
            ],
            'domain' => ['nullable', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:4000'],
            'callback_url' => ['nullable', 'url', 'max:190'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Coerce unchecked toggles and empty numeric fields before validation.
     */
    protected function prepareForValidation(): void
    {
        $sortOrder = $this->input('sort_order');

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'category_id' => $this->input('category_id') === '' ? null : $this->input('category_id'),
            'sort_order' => ($sortOrder === '' || $sortOrder === null) ? 0 : $sortOrder,
        ]);
    }
}
