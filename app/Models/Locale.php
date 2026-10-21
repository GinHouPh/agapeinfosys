<?php

namespace App\Models;

use Database\Factories\LocaleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * A language the site can be served in.
 */
class Locale extends Model
{
    /** @use HasFactory<LocaleFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'direction',
        'is_default',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The translations written in this language.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }

    /**
     * Locales that are switched on.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * The locale the site falls back to.
     */
    public static function default(): ?self
    {
        return static::query()
            ->active()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();
    }

    /**
     * The active locales, in the order they are offered.
     *
     * @return Collection<int, self>
     */
    public static function active(): Collection
    {
        return static::query()
            ->active()
            ->orderByDesc('is_default')
            ->orderBy('code')
            ->get();
    }
}
