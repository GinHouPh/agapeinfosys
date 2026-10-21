<?php

namespace App\Enums;

/**
 * Publishing state of a builder-managed page.
 */
enum PageStatus: string
{
    case Draft = 'draft';

    case Published = 'published';

    /** Saved with a future date; the page goes live on its own. */
    case Scheduled = 'scheduled';

    public function isPublished(): bool
    {
        return $this === self::Published;
    }

    public function isDraft(): bool
    {
        return $this === self::Draft;
    }

    public function isScheduled(): bool
    {
        return $this === self::Scheduled;
    }

    /**
     * Badge class used across the admin tables.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Published => 'badge--on',
            self::Draft, self::Scheduled => 'badge--off',
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
            self::Scheduled => 'Scheduled',
        };
    }
}
