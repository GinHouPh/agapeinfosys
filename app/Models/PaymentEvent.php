<?php

namespace App\Models;

use Database\Factories\PaymentEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentEvent extends Model
{
    /** @use HasFactory<PaymentEventFactory> */
    use HasFactory;

    protected $fillable = [
        'payment_gateway_id',
        'order_id',
        'shop_order_id',
        'type',
        'direction',
        'payload',
        'signature',
        'is_verified',
        'is_processed',
        'received_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'is_verified' => 'boolean',
            'is_processed' => 'boolean',
            'received_at' => 'datetime',
        ];
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shopOrder(): BelongsTo
    {
        return $this->belongsTo(ShopOrder::class);
    }
}
