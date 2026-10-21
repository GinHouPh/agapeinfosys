<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Writes an audit trail entry for the admin performing an action.
 */
trait RecordsActivity
{
    /**
     * Record one action in the activity log.
     *
     * @param  array<string, mixed>  $properties
     */
    protected function recordActivity(string $action, ?Model $subject = null, ?string $description = null, array $properties = []): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        ActivityLog::query()->create([
            'user_id' => $user->getKey(),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'action' => $action,
            'description' => $description,
            'properties' => $properties === [] ? null : $properties,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 400),
        ]);
    }
}
