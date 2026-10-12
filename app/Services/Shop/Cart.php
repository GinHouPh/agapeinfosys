<?php

namespace App\Services\Shop;

use App\Models\Plan;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Session-backed cart of plans, keyed by product + plan pair.
 *
 * Only ids are stored; prices are always re-read from the plans table so a
 * stale session can never change what gets charged.
 */
class Cart
{
    private const SESSION_KEY = 'shop.cart';

    /**
     * Cart entries as [product_id, plan_id] pairs.
     *
     * @return array<int, array{product_id: int, plan_id: int}>
     */
    public function items(): array
    {
        $items = session(self::SESSION_KEY, []);

        return is_array($items) ? $items : [];
    }

    public function count(): int
    {
        return count($this->items());
    }

    public function hasProduct(int $productId): bool
    {
        foreach ($this->items() as $item) {
            if ((int) $item['product_id'] === $productId) {
                return true;
            }
        }

        return false;
    }

    public function add(Product $product, Plan $plan): void
    {
        $items = $this->items();
        $items[$this->key($product, $plan)] = [
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
        ];

        session([self::SESSION_KEY => $items]);
    }

    public function remove(int $productId, int $planId): void
    {
        $items = $this->items();

        unset($items[$productId.':'.$planId]);

        session([self::SESSION_KEY => $items]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Resolved cart lines: still-active products and plans only.
     *
     * Stale entries (deleted or deactivated products) are skipped here and
     * silently dropped on the next checkout attempt.
     *
     * @return Collection<int, array{key: string, product: Product, plan: Plan, price: int}>
     */
    public function lines(): Collection
    {
        $items = $this->items();

        if ($items === []) {
            return collect();
        }

        $products = Product::query()
            ->active()
            ->whereIn('id', array_unique(array_column($items, 'product_id')))
            ->get()
            ->keyBy('id');

        $plans = Plan::query()
            ->active()
            ->whereIn('id', array_unique(array_column($items, 'plan_id')))
            ->get()
            ->keyBy('id');

        return collect($items)
            ->map(function (array $item) use ($products, $plans): ?array {
                $product = $products->get((int) $item['product_id']);
                $plan = $plans->get((int) $item['plan_id']);

                if ($product === null || $plan === null || $plan->product_id !== $product->getKey()) {
                    return null;
                }

                return [
                    'key' => $product->getKey().':'.$plan->getKey(),
                    'product' => $product,
                    'plan' => $plan,
                    'price' => $plan->price,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Sum of all line prices in minor units.
     */
    public function subtotal(): int
    {
        return (int) $this->lines()->sum('price');
    }

    private function key(Product $product, Plan $plan): string
    {
        return $product->getKey().':'.$plan->getKey();
    }
}
