<?php

namespace App\Enums;

/**
 * Lifecycle of the entitlement a product reads back from us.
 */
enum SubscriptionStatus: string
{
    case Active = 'active';

    case Cancelled = 'cancelled';

    case Expired = 'expired';

    /**
     * Whether the entitlement currently grants access.
     */
    public function grantsAccess(): bool
    {
        return $this === self::Active;
    }

    /**
     * Badge class used across the admin tables.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'badge--on',
            self::Cancelled => 'badge--off',
            self::Expired => 'badge--off',
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
