<?php

namespace App\Models;

use Database\Factories\ActivityLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * An audit trail of who changed what in the admin area.
 */
class ActivityLog extends Model
{
    /** @use HasFactory<ActivityLogFactory> */
    use HasFactory;

    /**
     * The table is singular by design — the audit trail is one log.
     *
     * @var string
     */
    protected $table = 'activity_log';

    protected $fillable = [
        'user_id',
        'subject_type',
        'subject_id',
        'action',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    /**
     * The admin who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The record the action was performed on.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Only entries recorded for a given action.
     */
    public function scopeAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    /**
     * Only entries recorded for a given subject.
     */
    public function scopeForSubject(Builder $query, Model $subject): Builder
    {
        return $query
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey());
    }
}
