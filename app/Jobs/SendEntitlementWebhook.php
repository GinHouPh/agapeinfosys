<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

/**
 * Push a signed entitlement update to a product's callback URL.
 *
 * The product can also poll the query API, so a failed push only delays the
 * update — retries make it near-realtime in practice.
 */
class SendEntitlementWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Retry transient failures with growing gaps for up to ~15 minutes.
     *
     * @var array<int, int>
     */
    public array $backoff = [30, 120, 600];

    public int $timeout = 20;

    public function __construct(
        public readonly int $orderId,
        public readonly string $event = 'subscription.updated',
    ) {}

    public function handle(): void
    {
        $order = Order::query()->with(['product', 'plan'])->find($this->orderId);

        if ($order === null) {
            return;
        }

        $product = $order->product;

        if ($product === null || $product->callback_url === null || $product->callback_url === '') {
            return;
        }

        $subscription = $order->subscription()->with('plan')->first();

        $timestamp = (string) now()->timestamp;

        $payload = [
            'event' => $this->event,
            'product' => $product->slug,
            'order' => $order->toPublicArray(),
            'subscription' => $subscription?->toPublicArray(),
            'timestamp' => $timestamp,
        ];

        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        Http::withHeaders([
            'X-Agape-Key' => $product->api_key,
            'X-Agape-Timestamp' => $timestamp,
            'X-Agape-Signature' => hash_hmac(
                'sha256',
                $timestamp.'.POST.'.$this->path($product->callback_url),
                (string) $product->api_secret
            ),
            'Content-Type' => 'application/json',
        ])
            ->connectTimeout(5)
            ->timeout(15)
            ->withBody($body, 'application/json')
            ->post($product->callback_url)
            ->throw();
    }

    /**
     * Path component of a callback URL, used in the signature.
     */
    private function path(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        return is_string($path) && $path !== '' ? ltrim($path, '/') : '';
    }
}
