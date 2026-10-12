<?php

namespace App\Enums;

use Illuminate\Support\Str;

/**
 * How a plan is billed: a single payment or an auto-renewing cycle.
 */
enum BillingType: string
{
    case OneTime = 'one_time';

    case Recurring = 'recurring';

    /**
     * Labels used by the admin forms.
     */
    public function label(): string
    {
        return match ($this) {
            self::OneTime => 'One-time purchase',
            self::Recurring => 'Recurring subscription',
        };
    }

    /**
     * Human friendly period for a recurring interval expressed in days.
     */
    public static function intervalLabel(int $days): string
    {
        return match (true) {
            $days % 365 === 0 && $days >= 365 => Str::plural('year', intdiv($days, 365)),
            $days % 30 === 0 && $days >= 30 => Str::plural('month', intdiv($days, 30)),
            $days % 7 === 0 && $days >= 7 => Str::plural('week', intdiv($days, 7)),
            default => Str::plural('day', $days),
        };
    }
}
