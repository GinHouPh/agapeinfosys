<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Models\Concerns\FormatsMoney;
use App\Services\Payments\Contracts\Payable;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model implements Payable
{
    /** @use HasFactory<OrderFactory> */
    use FormatsMoney, HasFactory;

    protected $fillable = [
        'product_id',
        'plan_id',
        'payment_gateway_id',
        'reference',
        'subscriber_key',
        'name',
        'email',
        'phone',
        'amount',
        'currency',
        'status',
        'gateway_order_id',
        'gateway_payment_id',
        'gateway_subscription_id',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'last_order_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(PaymentEvent::class)->orderByDesc('id');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Paid);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Pending);
    }

    /**
     * Short human-facing order number, e.g. "AGP-7F3K2Q".
     */
    public static function generateReference(): string
    {
        return 'AGP-'.Str::upper(Str::random(6));
    }

    public function formattedAmount(): string
    {
        return $this->formatMoney($this->amount, $this->currency);
    }

    /**
     * Whether a pending order has passed its payment window.
     */
    public function hasExpired(): bool
    {
        return $this->status === OrderStatus::Pending
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    /**
     * Claim the order for completion, guarding against concurrent webhooks.
     */
    public function claimForCompletion(): bool
    {
        return static::query()
            ->whereKey($this->getKey())
            ->where('status', OrderStatus::Pending)
            ->update(['status' => OrderStatus::Paid, 'paid_at' => now()]) === 1;
    }

    /**
     * Full public state of the order for API responses.
     *
     * @return array<string, mixed>
     */
    public function toPublicArray(): array
    {
        return [
            'reference' => $this->reference,
            'status' => $this->status->value,
            'product' => $this->product?->slug,
            'plan' => [
                'code' => $this->plan?->code,
                'name' => $this->plan?->name,
            ],
            'subscriber_key' => $this->subscriber_key,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'amount_display' => $this->formattedAmount(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    /*
    |----------------------------------------------------------------------
    | Payable — how the gateway drivers see this order
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
        return (string) ($this->plan?->name ?? 'Plan');
    }

    public function paymentBrand(): string
    {
        return (string) ($this->product?->name ?? config('app.name'));
    }

    public function paymentReturnUrl(): string
    {
        return route('checkout.result', [$this->product, $this]);
    }

    public function paymentContext(): string
    {
        return (string) ($this->product?->slug ?? '');
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
