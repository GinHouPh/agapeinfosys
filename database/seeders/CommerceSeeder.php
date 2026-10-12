<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use App\Models\Plan;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CommerceSeeder extends Seeder
{
    /**
     * Seed the digital products, their plans and the gateway records.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Tongdon', 'slug' => 'tongdon', 'domain' => 'tongdon.com'],
            ['name' => 'SMT', 'slug' => 'smt', 'domain' => 'smt.com'],
            ['name' => 'MotionMix', 'slug' => 'motionmix', 'domain' => 'motionmix.tv'],
            ['name' => 'Ekklesia', 'slug' => 'ekklesia', 'domain' => 'ekklesia.org'],
        ];

        foreach ($products as $index => $data) {
            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'domain' => $data['domain'],
                    'description' => $data['name'].' premium features, unlocked with a subscription.',
                    'callback_url' => 'https://'.$data['domain'].'/webhooks/agape',
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );

            Plan::updateOrCreate(
                ['product_id' => $product->getKey(), 'code' => 'monthly'],
                [
                    'name' => 'Monthly',
                    'description' => 'Full access, billed every month.',
                    'billing_type' => 'recurring',
                    'price' => 29900,
                    'currency' => 'INR',
                    'interval_days' => 30,
                    'validity_days' => null,
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );

            Plan::updateOrCreate(
                ['product_id' => $product->getKey(), 'code' => 'yearly'],
                [
                    'name' => 'Yearly',
                    'description' => 'Full access for a year — two months free.',
                    'billing_type' => 'one_time',
                    'price' => 299900,
                    'currency' => 'INR',
                    'validity_days' => 365,
                    'interval_days' => null,
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );

            Plan::updateOrCreate(
                ['product_id' => $product->getKey(), 'code' => 'lifetime'],
                [
                    'name' => 'Lifetime',
                    'description' => 'Pay once, keep access forever.',
                    'billing_type' => 'one_time',
                    'price' => 999900,
                    'currency' => 'INR',
                    'validity_days' => 3650,
                    'interval_days' => null,
                    'is_active' => true,
                    'sort_order' => 2,
                ]
            );
        }

        $gateways = [
            ['driver' => 'cashfree', 'name' => 'Cashfree'],
            ['driver' => 'phonepe', 'name' => 'PhonePe'],
            ['driver' => 'payu', 'name' => 'PayU'],
            ['driver' => 'paypal', 'name' => 'PayPal'],
            ['driver' => 'sandbox', 'name' => 'Sandbox (simulator)'],
        ];

        foreach ($gateways as $gateway) {
            PaymentGateway::updateOrCreate(
                ['driver' => $gateway['driver']],
                [
                    'name' => $gateway['name'],
                    'is_enabled' => $gateway['driver'] === 'sandbox',
                    'is_default' => $gateway['driver'] === 'sandbox',
                    'mode' => 'test',
                ]
            );
        }
    }
}
