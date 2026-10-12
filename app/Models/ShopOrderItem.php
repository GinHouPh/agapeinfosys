<?php

namespace App\Models;

use Database\Factories\ShopOrderItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single plan line of a cart checkout, with its price snapshot.
 */
class ShopOrderItem extends Model
{
    /** @use HasFactory<ShopOrderItemFactory> */
    use HasFactory;

    protected $fillable = [
        'shop_order_id',
        'product_id',
        'plan_id',
        'product_name',
        'plan_name',
        'unit_price',
        'total',
        'currency',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'integer',
            'total' => 'integer',
        ];
    }

    public function shopOrder(): BelongsTo
    {
        return $this->belongsTo(ShopOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
