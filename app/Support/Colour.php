<?php

namespace App\Support;

/**
 * Small colour helpers for the design editor.
 */
class Colour
{
    /**
     * The hex value a colour picker can show.
     *
     * A picker only understands hex, so an rgba() token — the accent soft
     * shade, for instance — is flattened against black. It is a preview
     * affordance only; the token itself keeps its authored value.
     */
    public static function toHex(string $value): string
    {
        $value = trim($value);

        if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $value) === 1) {
            return strtolower($value);
        }

        if (preg_match('/^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})/i', $value, $matches) === 1) {
            return sprintf(
                '#%02x%02x%02x',
                min(255, (int) $matches[1]),
                min(255, (int) $matches[2]),
                min(255, (int) $matches[3]),
            );
        }

        return '#000000';
    }
}
