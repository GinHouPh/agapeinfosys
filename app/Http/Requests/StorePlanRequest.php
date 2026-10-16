<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $planId = $this->route('plan')?->getKey();

        return [
            'product_id' => ['required', 'exists:products,id'],
            'name' => ['required', 'string', 'max:160'],
            'code' => [
                'required',
                'string',
                'max:80',
                'alpha_dash',
                Rule::unique('plans', 'code')->where('product_id', $this->input('product_id'))->ignore($planId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'billing_type' => ['required', Rule::in(['one_time', 'recurring'])],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'currency' => ['required', 'string', 'size:3'],
            'validity_days' => ['nullable', 'integer', 'min:0', 'max:36500'],
            'interval_days' => ['nullable', 'integer', 'min:1', 'max:36500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $validityValue = $this->input('validity_value');
        $validityUnit = $this->input('validity_unit');
        $validityDays = $this->input('validity_days');

        if ($validityUnit === 'lifetime' || (is_numeric($validityValue) && (int) $validityValue === 0 && $validityUnit === 'lifetime')) {
            $validityDays = 0;
        } elseif (is_numeric($validityValue) && in_array($validityUnit, ['days', 'months', 'years'], true)) {
            $value = (int) $validityValue;
            $validityDays = match ($validityUnit) {
                'months' => $value * 30,
                'years' => $value * 365,
                default => $value,
            };
        }

        $intervalValue = $this->input('interval_value');
        $intervalUnit = $this->input('interval_unit');
        $intervalDays = $this->input('interval_days');
        if (is_numeric($intervalValue) && in_array($intervalUnit, ['days', 'months', 'years'], true) && (int) $intervalValue >= 0) {
            $value = (int) $intervalValue;
            $intervalDays = match ($intervalUnit) {
                'months' => $value * 30,
                'years' => $value * 365,
                default => $value,
            };
        }

        $priceInput = $this->input('price');
        $priceMinor = null;
        if ($priceInput !== null && $priceInput !== '') {
            $priceMinor = (int) round((float) $priceInput * 100);
        }

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->input('sort_order') === null || $this->input('sort_order') === '' ? 0 : $this->input('sort_order'),
            'validity_days' => $validityDays === null || $validityDays === '' ? null : (int) $validityDays,
            'interval_days' => $intervalDays === null || $intervalDays === '' ? null : (int) $intervalDays,
            'description' => $this->input('description') === '' ? null : $this->input('description'),
            'price' => $priceMinor,
        ]);
    }
}
