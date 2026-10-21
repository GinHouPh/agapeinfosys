<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Database\Factories\DesignThemeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A set of design token values the whole site renders with.
 *
 * The tokens carry values, never names to store elsewhere: the builder
 * still stores token names, and this theme decides what each name resolves
 * to.
 */
class DesignTheme extends Model
{
    /** @use HasFactory<DesignThemeFactory> */
    use GeneratesSlug, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_default',
        'is_active',
        'tokens',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tokens' => 'array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The slug is built from the theme name, not a title.
     *
     * @var list<string>
     */
    protected $slugSource = ['name'];

    /**
     * The admin who created the theme.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Themes that are switched on.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * The theme the site renders with, if one is active.
     */
    public static function active(): ?self
    {
        return static::query()
            ->active()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * The value of one token in one scheme, falling back to the defaults.
     */
    public function token(string $name, string $scheme = 'dark'): ?string
    {
        $value = $this->tokens[$scheme][$name] ?? null;

        if (is_string($value) && $value !== '') {
            return $value;
        }

        $fallback = config('design.tokens.'.$scheme.'.'.$name);

        return is_string($fallback) ? $fallback : null;
    }

    /**
     * The font stack for one slot.
     */
    public function font(string $slot): ?string
    {
        $value = $this->tokens['font'][$slot] ?? null;

        if (is_string($value) && $value !== '') {
            return $value;
        }

        $fallback = config('design.fonts.'.$slot) ?? [];
        $first = array_key_first($fallback);

        return is_string($first) ? $first : null;
    }
}
