<?php

namespace App\Services;

use App\Builder\DesignTokens;
use App\Models\DesignTheme;

/**
 * Turns a theme's token values into the CSS custom properties the site reads.
 *
 * The compiled block is emitted inline in the document head, before the
 * stylesheet, so it beats the `@theme` output of Tailwind's cascade. Every
 * utility built on a token — `text-accent`, `bg-surface`, `border-line` —
 * repaints at once.
 *
 * Colours are per scheme. Fonts, radii and shadows are global and live in
 * the root block.
 */
class ThemeCompiler
{
    /**
     * The stylesheet for a theme, ready to sit inside a <style> tag.
     */
    public function compile(DesignTheme $theme): string
    {
        $blocks = [];

        $root = array_merge(
            $this->colourDeclarations($theme, 'dark'),
            $this->globalDeclarations($theme),
        );

        if ($root !== []) {
            $blocks[] = ':root{'.implode(';', $root).'}';
        }

        foreach ($this->schemes() as $scheme => $selector) {
            if ($scheme === 'dark') {
                continue;
            }

            $declarations = $this->colourDeclarations($theme, $scheme);

            if ($declarations === []) {
                continue;
            }

            $blocks[] = $selector.'{'.implode(';', $declarations).'}';
        }

        return implode("\n", $blocks);
    }

    /**
     * The inline style tag for the active theme, or null when there is none.
     */
    public function styleTag(): ?string
    {
        $theme = DesignTheme::active();

        if ($theme === null) {
            return null;
        }

        $css = $this->compile($theme);

        return $css === '' ? null : '<style id="design-theme">'.$css.'</style>';
    }

    /**
     * The inline style tag for a page's own token overrides.
     *
     * A page may change the accent it renders with without changing the
     * theme every other page uses. The overrides are emitted after the
     * theme, so they win.
     *
     * @param  array<string, array<string, string>>  $overrides
     */
    public function pageTag(array $overrides): ?string
    {
        if ($overrides === []) {
            return null;
        }

        $blocks = [];

        foreach ($overrides as $scheme => $values) {
            $declarations = [];

            foreach ($values as $name => $value) {
                $declarations[] = '--color-'.$name.':'.$value;
            }

            if ($declarations === []) {
                continue;
            }

            $selector = ($scheme === 'light' ? 'html.light' : ':root');

            $blocks[] = $selector.'{'.implode(';', $declarations).'}';
        }

        if ($blocks === []) {
            return null;
        }

        return '<style id="page-tokens">'.implode("\n", $blocks).'</style>';
    }

    /**
     * The colour declarations for one scheme.
     *
     * @return array<int, string>
     */
    public function colourDeclarations(DesignTheme $theme, string $scheme): array
    {
        $declarations = [];

        foreach (array_keys(DesignTokens::COLORS) as $name) {
            $value = $theme->token($name, $scheme);

            if ($value !== null) {
                $declarations[] = '--color-'.$name.':'.$value;
            }
        }

        return $declarations;
    }

    /**
     * The declarations that are not scheme specific — fonts, radii, shadows.
     *
     * @return array<int, string>
     */
    public function globalDeclarations(DesignTheme $theme): array
    {
        $declarations = [];

        foreach (['display' => '--font-display', 'body' => '--font-body'] as $slot => $property) {
            $font = $theme->font($slot);

            if ($font !== null) {
                $family = preg_replace('/[^A-Za-z0-9 \-]/', '', $font) ?? $font;
                $declarations[] = $property.":'".trim($family)."', ui-sans-serif, system-ui, sans-serif";
            }
        }

        foreach (['radius', 'shadow'] as $scale) {
            $steps = config('design.scales.'.$scale, []);
            $chosen = $theme->tokens[$scale] ?? [];

            if (! is_array($chosen)) {
                continue;
            }

            foreach ($chosen as $key => $step) {
                $value = $steps[$step] ?? null;

                if (is_string($value)) {
                    $declarations[] = '--'.$scale.'-'.$key.':'.$value;
                }
            }
        }

        return $declarations;
    }

    /**
     * The colour schemes a theme carries.
     *
     * @return array<string, string>
     */
    private function schemes(): array
    {
        return config('design.schemes', ['dark' => ':root', 'light' => 'html.light']);
    }
}
