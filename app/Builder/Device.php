<?php

namespace App\Builder;

/**
 * The tiers a responsive control can be authored for, and where each one lives.
 *
 * Desktop is the base: it is stored under the control's own key, so a widget
 * that has never been authored per-device keeps reading settings[key] and gets
 * exactly what it got before this existed. Tablet and mobile are sibling keys
 * that only exist when they differ, which makes "inherit" a matter of the key
 * being absent rather than a sentinel value every reader would have to
 * recognise — the sanitizer drops an empty override instead of pinning it, so
 * the panel can forget a value simply by deleting it.
 *
 * Rendering is mobile-first, matching the ladder the markup already speaks
 * (md at 768, lg at 1024, fixed by the section-rhythm change): mobile carries
 * no prefix because it is the base, tablet is md:, desktop is lg:.
 *
 * The class is the single source of the vocabulary — the sanitizer picks which
 * sibling keys a control may carry, the widget views pick the prefix each tier
 * renders with, and the editor panel is handed both lists straight from here
 * so the three can never drift apart.
 */
final class Device
{
    /**
     * Tiers, ordered from the one every other tier falls back to.
     *
     * @var array<int, string>
     */
    public const TIERS = ['desktop', 'tablet', 'mobile'];

    /**
     * Storage suffix appended to a control's key. Desktop's is empty on
     * purpose: the base key *is* the desktop value.
     *
     * @var array<string, string>
     */
    public const SUFFIX = [
        'desktop' => '',
        'tablet' => '_tablet',
        'mobile' => '_mobile',
    ];

    /**
     * Tailwind min-width variant that switches a tier's class on. Mobile is
     * the empty prefix because mobile-first CSS starts at the base.
     *
     * @var array<string, string>
     */
    public const VARIANT = [
        'desktop' => 'lg',
        'tablet' => 'md',
        'mobile' => '',
    ];

    /**
     * The settings key a tier of a control is stored under.
     */
    public static function key(string $controlKey, string $tier): string
    {
        return $controlKey.(self::SUFFIX[$tier] ?? '');
    }

    /**
     * The keys a responsive control keeps beside its base — the tiers that can
     * override it. The sanitizer walks these instead of guessing at suffixes.
     *
     * @return array<int, string>
     */
    public static function overrides(string $controlKey): array
    {
        $keys = [];

        foreach (self::TIERS as $tier) {
            if ($tier === 'desktop') {
                continue;
            }

            $keys[] = self::key($controlKey, $tier);
        }

        return $keys;
    }

    /**
     * The value authored for one tier of a control, or null when that tier
     * has no override and should follow the tier it inherits from.
     *
     * Blank is treated as absent: an override nobody filled in is not a value,
     * and coercing it would silently pin the tier to the control's default
     * instead of letting it fall back.
     *
     * @param  array<string, mixed>  $settings
     */
    public static function read(array $settings, string $controlKey, string $tier): ?string
    {
        $value = $settings[self::key($controlKey, $tier)] ?? null;

        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * Every per-device key a set of controls can carry.
     *
     * The renderer filters settings down to the keys a widget declares, and a
     * sibling key is not a declared control — it exists only when a tier has
     * been authored, so it has to be named here to survive that pass.
     *
     * @param  array<int, array<string, mixed>>  $controls
     * @return array<int, string>
     */
    public static function keysFor(array $controls): array
    {
        $keys = [];

        foreach ($controls as $control) {
            if (($control['responsive'] ?? false) !== true) {
                continue;
            }

            $key = $control['key'] ?? null;

            if (! is_string($key) || $key === '') {
                continue;
            }

            foreach (self::overrides($key) as $override) {
                $keys[] = $override;
            }
        }

        return $keys;
    }
}
