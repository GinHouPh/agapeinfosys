<?php

namespace App\Models;

use Database\Factories\PaymentGatewayFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentGateway extends Model
{
    /** @use HasFactory<PaymentGatewayFactory> */
    use HasFactory;

    protected $fillable = [
        'driver',
        'name',
        'is_enabled',
        'is_default',
        'mode',
        'test_config',
        'live_config',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
            'test_config' => 'array',
            'live_config' => 'array',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Credentials for the gateway's active mode.
     *
     * @return array<string, mixed>
     */
    public function credentials(): array
    {
        return ($this->mode === 'live' ? $this->live_config : $this->test_config) ?? [];
    }

    /**
     * Whether the active mode carries credentials.
     */
    public function isConfigured(): bool
    {
        return $this->credentials() !== [];
    }

    public function isLive(): bool
    {
        return $this->mode === 'live';
    }

    /**
     * Checkout page script for gateways that need a browser SDK.
     */
    public function checkoutScript(): ?string
    {
        return $this->driver === 'cashfree'
            ? 'https://sdk.cashfree.com/js/v3/cashfree.js'
            : null;
    }
}
