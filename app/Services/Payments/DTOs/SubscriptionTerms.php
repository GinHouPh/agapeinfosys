<?php

namespace App\Services\Payments\DTOs;

use App\Services\Payments\Contracts\Payable;
use InvalidArgumentException;

/**
 * The recurring terms a payable hands to a subscription-capable driver.
 *
 * A null on {@see Payable::paymentSubscriptionTerms()}
 * means a one-time charge; returning these terms switches the driver to the
 * provider's mandate/subscription API.
 */
final class SubscriptionTerms
{
    /**
     * Cashfree interval types.
     *
     * @var array<int, string>
     */
    private const INTERVAL_TYPES = ['DAY', 'WEEK', 'MONTH', 'YEAR'];

    public function __construct(
        public readonly string $planId,
        public readonly string $planName,
        public readonly string $intervalType,
        public readonly int $intervals,
        public readonly int $recurringAmount,
        public readonly int $maxAmount,
        public readonly string $currency,
    ) {
        if (! in_array($intervalType, self::INTERVAL_TYPES, true)) {
            throw new InvalidArgumentException('Unsupported subscription interval type "'.$intervalType.'".');
        }

        if ($intervals < 1) {
            throw new InvalidArgumentException('A subscription interval must repeat at least once.');
        }
    }

    /**
     * Map a day count to Cashfree's interval type and multiplier, e.g. 90
     * becomes [MONTH, 3] and 30 becomes [MONTH, 1].
     *
     * @return array{0: string, 1: int}
     */
    public static function cadenceFromDays(int $days): array
    {
        return match (true) {
            $days >= 365 && $days % 365 === 0 => ['YEAR', intdiv($days, 365)],
            $days >= 30 && $days % 30 === 0 => ['MONTH', intdiv($days, 30)],
            $days >= 7 && $days % 7 === 0 => ['WEEK', intdiv($days, 7)],
            default => ['DAY', max(1, $days)],
        };
    }
}
