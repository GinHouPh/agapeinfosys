<?php

namespace App\Models;

use App\Enums\BillingType;
use App\Models\Concerns\FormatsMoney;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use FormatsMoney, HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'code',
        'description',
        'billing_type',
        'price',
        'currency',
        'validity_days',
        'interval_days',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'billing_type' => BillingType::class,
            'price' => 'integer',
            'validity_days' => 'integer',
            'interval_days' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Price rendered for humans, e.g. "₹4,999.00".
     */
    public function formattedPrice(): string
    {
        return $this->formatMoney($this->price, $this->currency);
    }

    /**
     * Period a payment grants access for, in days.
     */
    public function entitlementDays(): int
    {
        if ($this->billing_type === BillingType::Recurring) {
            return $this->interval_days ?? 30;
        }

        return $this->validity_days ?? 365;
    }

    /**
     * Whether a renewal order extends this plan's entitlement.
     */
    public function isRecurring(): bool
    {
        return $this->billing_type === BillingType::Recurring;
    }
}
