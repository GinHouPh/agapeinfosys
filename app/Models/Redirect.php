<?php

namespace App\Models;

use Database\Factories\RedirectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A permanent or temporary redirect from one path to another.
 */
class Redirect extends Model
{
    /** @use HasFactory<RedirectFactory> */
    use HasFactory;

    protected $fillable = [
        'from_path',
        'to_path',
        'status_code',
        'is_active',
        'hits',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'is_active' => 'boolean',
            'hits' => 'integer',
        ];
    }

    /**
     * Redirects that are switched on.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
