<?php

namespace App\Enums;

/**
 * Lifecycle of a checkout order.
 */
enum OrderStatus: string
{
    case Pending = 'pending';

    case Paid = 'paid';

    case Failed = 'failed';

    case Expired = 'expired';

    case Refunded = 'refunded';

    /**
     * Terminal states that never transition again.
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::Paid, self::Refunded, self::Expired], true);
    }

    /**
     * Badge class used across the admin tables.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Paid => 'badge--on',
            self::Pending => 'badge--off',
            default => 'badge--off',
        };
    }

    /**
     * Copy for badges and labels.
     */
    public function label(): string
    {
        return ucfirst($this->value);
    }
}
