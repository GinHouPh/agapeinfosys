<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\ShopOrder;
use App\Models\ShopOrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShopOrderItem>
 */
class ShopOrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plan = Plan::factory()->create();

        return [
            'shop_order_id' => ShopOrder::factory(),
            'product_id' => $plan->product_id,
            'plan_id' => $plan->getKey(),
            'product_name' => $plan->product?->name ?? 'Product',
            'plan_name' => $plan->name,
            'unit_price' => $plan->price,
            'total' => $plan->price,
            'currency' => $plan->currency,
        ];
    }
}
