<?php

namespace App\Models;

use Database\Factories\FeatureFlagFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A switch that turns a part of the platform on, off, or part-way.
 */
class FeatureFlag extends Model
{
    /** @use HasFactory<FeatureFlagFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'is_enabled',
        'rollout_percentage',
        'starts_at',
        'ends_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'rollout_percentage' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Flags that are switched on.
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Whether the flag is inside its scheduled window.
     */
    public function isWithinSchedule(): bool
    {
        $now = now();

        if ($this->starts_at !== null && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at !== null && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Whether the flag is on for everyone, or for this visitor's share.
     */
    public function appliesTo(?string $bucket = null): bool
    {
        if (! $this->is_enabled || ! $this->isWithinSchedule()) {
            return false;
        }

        if ($this->rollout_percentage >= 100) {
            return true;
        }

        if ($this->rollout_percentage <= 0) {
            return false;
        }

        $bucket ??= (string) ($_SERVER['REMOTE_ADDR'] ?? 'local');

        return (crc32($bucket) % 100) < $this->rollout_percentage;
    }
}
