<?php

namespace App\Models;

use Database\Factories\PageViewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One request for a public page.
 *
 * The address is never stored. A hash of it is, so the same visitor can be
 * recognised without keeping anything that identifies an address.
 */
class PageView extends Model
{
    /** @use HasFactory<PageViewFactory> */
    use HasFactory;

    protected $fillable = [
        'path',
        'method',
        'status',
        'referrer',
        'visitor',
        'ip_hash',
        'user_agent',
        'user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }

    /**
     * The signed-in admin, when there was one.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Only views inside a period.
     */
    public function scopeBetween(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
