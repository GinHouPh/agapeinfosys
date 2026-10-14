<?php

namespace App\Builder;

/**
 * The colour palette an author may point a control at.
 *
 * A colour control stores the NAME of one of these tokens and never a colour
 * value. The widget view then turns that name into a Tailwind class bound to
 * the matching `--color-*` custom property, so the colour resolves through
 * the cascade instead of being frozen into the markup.
 *
 * That indirection is the whole point. The site has a light theme that
 * re-declares the same token names under `html.light`, and a rebrand would
 * change the same handful of declarations again; a stored hex would sit on
 * the repainted surface exactly as authored, unreadable, where a token
 * follows the theme and survives a rebrand untouched.
 *
 * The names must stay in step with `@theme` in resources/css/app.css. Nothing
 * in the runtime can check that — a token that stops existing in the
 * stylesheet still sanitises cleanly and then renders as whatever colour it
 * inherits — so DesignTokensTest holds the two together.
 */
class DesignTokens
{
    /**
     * Selectable colours, keyed by the name the stylesheet declares.
     *
     * @var array<string, string>
     */
    public const COLORS = [
        'accent' => 'Accent',
        'accent-soft' => 'Accent soft',
        'on-accent' => 'On accent',
        'headline' => 'Headline',
        'body' => 'Body',
        'muted' => 'Muted',
        'surface' => 'Surface',
        'box' => 'Box',
        'ink' => 'Ink',
        'line' => 'Line',
    ];

    /**
     * Where a control with no default of its own lands.
     */
    public const FALLBACK = 'accent';

    /**
     * Whether a value is one of the palette's token names.
     */
    public static function isColor(mixed $name): bool
    {
        return is_string($name) && array_key_exists($name, self::COLORS);
    }
}
