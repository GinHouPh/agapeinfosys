<?php

namespace App\Enums;

/**
 * Where a menu is rendered on the public site.
 *
 * A location maps to one place in the layout. Several menus may share a
 * location, but only the first active one is rendered.
 */
enum MenuLocation: string
{
    case Header = 'header';

    case Footer = 'footer';

    case Legal = 'legal';

    case Mobile = 'mobile';

    case Shop = 'shop';

    /**
     * The public label used in the admin menu list.
     */
    public function label(): string
    {
        return match ($this) {
            self::Header => 'Header',
            self::Footer => 'Footer',
            self::Legal => 'Legal footer',
            self::Mobile => 'Mobile dock',
            self::Shop => 'Shop',
        };
    }

    /**
     * The badge class used across the admin tables.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Header => 'badge--on',
            self::Footer, self::Legal => 'badge--off',
            self::Mobile, self::Shop => 'badge--off',
        };
    }
}
