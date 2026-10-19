<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Models\Concerns\FormatsMoney;
use App\Services\Payments\Contracts\Payable;
use App\Services\Payments\DTOs\SubscriptionTerms;
use Database\Factories\ShopOrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A cart checkout: one provider payment that fans out into per-product
 * orders and subscriptions once it has been confirmed.
 */
class ShopOrder extends Model implements Payable
{
    /** @use HasFactory<ShopOrderFactory> */
    use FormatsMoney, HasFactory;

    protected $fillable = [
        'reference',
        'payment_gateway_id',
        'subscriber_key',
        'name',
        'email',
        'phone',
        'amount',
        'currency',
        'status',
        'gateway_order_id',
        'gateway_payment_id',
        'gateway_payload',
        'paid_at',
        'expires_at',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'amount' => 'integer',
            'gateway_payload' => 'array',
            'meta' => 'array',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $order): void {
            if ($order->reference === null || $order->reference === '') {
                $order->reference = self::generateReference();
            }
        });
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShopOrderItem::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PaymentEvent::class)->orderByDesc('id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Pending);
    }

    /**
     * Short human-facing cart order number, e.g. "AGP-S-7F3K2Q".
     */
    public static function generateReference(): string
    {
        return 'AGP-S-'.Str::upper(Str::random(6));
    }

    public function formattedAmount(): string
    {
        return $this->formatMoney($this->amount, $this->currency);
    }

    /**
     * Whether a pending cart order has passed its payment window.
     */
    public function hasExpired(): bool
    {
        return $this->status === OrderStatus::Pending
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    /**
     * Claim the cart order for completion, guarding against concurrent webhooks.
     */
    public function claimForCompletion(): bool
    {
        return static::query()
            ->whereKey($this->getKey())
            ->where('status', OrderStatus::Pending)
            ->update(['status' => OrderStatus::Paid, 'paid_at' => now()]) === 1;
    }

    /*
    |----------------------------------------------------------------------
    | Payable — how the gateway drivers see this cart order
    |----------------------------------------------------------------------
    */

    public function paymentReference(): string
    {
        return (string) $this->reference;
    }

    public function paymentAmount(): int
    {
        return $this->amount;
    }

    public function paymentCurrency(): string
    {
        return (string) $this->currency;
    }

    public function paymentAmountDisplay(): string
    {
        return $this->formattedAmount();
    }

    public function paymentDescription(): string
    {
        $plans = $this->items->pluck('plan_name')->unique()->values();

        if ($plans->isEmpty()) {
            return 'Shop order';
        }

        return $plans->take(3)->implode(', ')
            .($plans->count() > 3 ? ' +'.($plans->count() - 3) : '');
    }

    public function paymentBrand(): string
    {
        return (string) config('app.name');
    }

    public function paymentReturnUrl(): string
    {
        return route('shop.result', $this);
    }

    public function paymentContext(): string
    {
        return 'shop';
    }

    public function paymentSubscriberKey(): ?string
    {
        return $this->subscriber_key;
    }

    public function paymentName(): ?string
    {
        return $this->name;
    }

    public function paymentEmail(): ?string
    {
        return $this->email;
    }

    public function paymentPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * A cart is always a single one-time charge; recurring items become
     * their own orders through the plan checkout instead.
     */
    public function paymentSubscriptionTerms(): ?SubscriptionTerms
    {
        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function paymentGatewayPayload(): array
    {
        return $this->gateway_payload ?? [];
    }

    public function gatewayOrderId(): ?string
    {
        return $this->gateway_order_id;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $payload
     */
    public function recordGatewayResult(array $attributes, array $payload = []): void
    {
        if ($payload !== []) {
            $attributes['gateway_payload'] = array_merge($this->gateway_payload ?? [], $payload);
        }

        $this->forceFill($attributes)->save();
    }
}
