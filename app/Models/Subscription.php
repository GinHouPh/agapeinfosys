<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'plan_id',
        'last_order_id',
        'subscriber_key',
        'name',
        'email',
        'status',
        'starts_at',
        'ends_at',
        'renews_at',
        'gateway_subscription_id',
        'cancelled_at',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'renews_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function lastOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'last_order_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatus::Active);
    }

    public function scopeForSubscriber(Builder $query, string $subscriberKey): Builder
    {
        return $query->where('subscriber_key', $subscriberKey);
    }

    /**
     * Recompute the status from the entitlement window.
     */
    public function refreshExpiry(): void
    {
        if ($this->status === SubscriptionStatus::Cancelled) {
            return;
        }

        if ($this->ends_at !== null && $this->ends_at->isPast()) {
            $this->forceFill(['status' => SubscriptionStatus::Expired])->save();
        }
    }

    /**
     * Whether the entitlement currently grants access.
     */
    public function isEntitled(): bool
    {
        $this->refreshExpiry();

        if ($this->status !== SubscriptionStatus::Active) {
            return false;
        }

        return $this->ends_at === null || $this->ends_at->isFuture();
    }

    /**
     * Full entitlement payload a product consumes.
     *
     * @return array<string, mixed>
     */
    public function toPublicArray(): array
    {
        return [
            'plan_code' => $this->plan?->code,
            'plan_name' => $this->plan?->name,
            'status' => $this->status->value,
            'entitled' => $this->isEntitled(),
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'renews_at' => $this->renews_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
        ];
    }
}
