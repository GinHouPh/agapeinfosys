<?php

namespace App\Enums;

/**
 * Delivery stage of a showcase project.
 */
enum ProjectStatus: string
{
    case Ongoing = 'ongoing';

    case Upcoming = 'upcoming';

    /**
     * Badge class used across the admin tables.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Ongoing => 'badge--on',
            self::Upcoming => 'badge--off',
        };
    }

    /**
     * Copy for badges and labels.
     */
    public function label(): string
    {
        return match ($this) {
            self::Ongoing => 'Ongoing',
            self::Upcoming => 'Upcoming',
        };
    }
}
