<?php

namespace App\Enums;

/**
 * Pipeline stage of a form submission.
 */
enum SubmissionStatus: string
{
    case New = 'new';

    case Reviewing = 'reviewing';

    case Contacted = 'contacted';

    case Quoted = 'quoted';

    case Declined = 'declined';

    /**
     * Badge class used across the admin tables.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::New => 'badge--on',
            self::Quoted => 'badge--on',
            default => 'badge--off',
        };
    }

    /**
     * Copy for badges and labels.
     */
    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Reviewing => 'Reviewing',
            self::Contacted => 'Contacted',
            self::Quoted => 'Quoted',
            self::Declined => 'Declined',
        };
    }
}
