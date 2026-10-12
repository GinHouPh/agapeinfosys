<?php

namespace App\Http\Controllers\Concerns;

/**
 * Turns textarea input (one item per line or comma) into a clean list.
 */
trait SplitsListInput
{
    /**
     * @return array<int, string>|null
     */
    protected function splitList(mixed $value): ?array
    {
        if (! is_string($value)) {
            return null;
        }

        if (trim($value) === '') {
            return null;
        }

        $items = preg_split('/[\r\n,]+/', $value) ?: [];
        $items = array_values(array_filter(array_map('trim', $items), fn (string $item) => $item !== ''));

        return $items === [] ? null : $items;
    }
}
