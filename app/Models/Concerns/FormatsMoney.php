<?php

namespace App\Models\Concerns;

use Illuminate\Support\Number;

/**
 * Renders integer minor-unit amounts as locale aware currency strings.
 */
trait FormatsMoney
{
    /**
     * Format a minor-unit amount (paise/cents) for display, e.g. "₹4,999.00".
     */
    protected function formatMoney(int $amount, string $currency): string
    {
        return Number::currency($amount / 100, $currency);
    }
}
