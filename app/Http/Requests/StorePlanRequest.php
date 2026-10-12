<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanRequest extends FormRequest
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
        $planId = $this->route('plan')?->getKey();

        return [
            'product_id' => ['required', 'exists:products,id'],
            'name' => ['required', 'string', 'max:160'],
            'code' => ['required', 'string', 'max:80', 'alpha_dash',
                Rule::unique('plans', 'code')->where('product_id', $this->input('product_id'))->ignore($planId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'billing_type' => ['required', Rule::in(['one_time', 'recurring'])],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'currency' => ['required', 'string', 'size:3'],
            'validity_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'interval_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
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
            'validity_days' => $this->input('validity_days') === '' ? null : $this->input('validity_days'),
            'interval_days' => $this->input('interval_days') === '' ? null : $this->input('interval_days'),
            'description' => $this->input('description') === '' ? null : $this->input('description'),
        ]);
    }
}
