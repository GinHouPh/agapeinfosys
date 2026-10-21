<?php

namespace App\Enums;

/**
 * How a menu item resolves its destination.
 */
enum MenuItemType: string
{
    /** A named Laravel route, e.g. "services.index". */
    case Route = 'route';

    /** An absolute or relative URL. */
    case Url = 'url';

    /** A builder-managed page, matched on its slug. */
    case Page = 'page';

    /** A published content record, matched on its slug. */
    case Record = 'record';

    /** A shop category, matched on its slug. */
    case Category = 'category';

    /**
     * The public label used in the admin editor.
     */
    public function label(): string
    {
        return match ($this) {
            self::Route => 'Route',
            self::Url => 'URL',
            self::Page => 'Page',
            self::Record => 'Record',
            self::Category => 'Category',
        };
    }

    /**
     * The options offered for this link type in the admin editor.
     *
     * @return array<int, string>
     */
    public function choices(): array
    {
        return array_map(
            static fn (self $case): string => $case->value,
            self::cases(),
        );
    }
}
