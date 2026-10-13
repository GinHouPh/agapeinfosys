<?php

namespace App\Enums;

/**
 * Publishing state of a builder-managed page.
 */
enum PageStatus: string
{
    case Draft = 'draft';

    case Published = 'published';

    public function isPublished(): bool
    {
        return $this === self::Published;
    }

    public function isDraft(): bool
    {
        return $this === self::Draft;
    }

    /**
     * Badge class used across the admin tables.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Published => 'badge--on',
            self::Draft => 'badge--off',
        };
    }

    /**
     * Copy for badges and labels.
     */
    public function label(): string
    {
        return match ($this) {
            self::Published => 'Published',
            self::Draft => 'Draft',
        };
    }
}
