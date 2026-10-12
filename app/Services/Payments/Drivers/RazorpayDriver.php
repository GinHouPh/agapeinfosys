<?php

namespace App\Services\Payments\Drivers;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\Payments\Contracts\PaymentGatewayDriver;
use App\Services\Payments\DTOs\CheckoutSession;
use App\Services\Payments\Exceptions\GatewayException;
use App\Services\Payments\Exceptions\GatewayNotConfiguredException;
use App\Services\Payments\Exceptions\WebhookVerificationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Razorpay Standard Checkout — orders API, inline SDK callback, webhook signature.
 *
 * Verified against Razorpay's documented flow; use test keys in admin first.
 */
class RazorpayDriver implements PaymentGatewayDriver
{
    private const ORDERS_ENDPOINT = 'https://api.razorpay.com/v1/orders';

    public function __construct(private readonly PaymentGateway $gateway) {}

    public function driver(): string
    {
        return 'razorpay';
    }

    public function isConfigured(): bool
    {
        $credentials = $this->gateway->credentials();

        return ($credentials['key_id'] ?? '') !== '' && ($credentials['key_secret'] ?? '') !== '';
    }

    /**
     * Create a Razorpay order and return the inline checkout payload.
     */
    public function createCheckout(Order $order): CheckoutSession
    {
        $credentials = $this->requireCredentials();

        $response = Http::withBasicAuth($credentials['key_id'], $credentials['key_secret'])
            ->connectTimeout(5)
            ->timeout(20)
            ->acceptJson()
            ->post(self::ORDERS_ENDPOINT, [
                'amount' => $order->amount,
                'currency' => $order->currency,
                'receipt' => $order->reference,
                'notes' => [
                    'reference' => $order->reference,
                    'product' => $order->product?->slug,
                    'subscriber' => $order->subscriber_key,
                ],
            ]);

        if ($response->failed()) {
            throw new GatewayException(
                'Razorpay rejected the order: '.mb_substr($response->body(), 0, 300)
            );
        }

        $payload = $response->json();

        if (($payload['id'] ?? '') === '') {
            throw new GatewayException('Razorpay did not return an order id.');
        }

        $order->forceFill(['gateway_order_id' => $payload['id']])->save();

        return CheckoutSession::inline('razorpay', [
            'key' => $credentials['key_id'],
            'order_id' => $payload['id'],
            'amount' => $order->amount,
            'currency' => $order->currency,
            'name' => $order->product?->name ?? config('app.name'),
            'description' => $order->plan?->name,
            'reference' => $order->reference,
            'prefill' => [
                'name' => $order->name,
                'email' => $order->email,
                'contact' => $order->phone,
            ],
            'theme' => [
                'color' => '#d90429',
            ],
        ]);
    }

    /**
     * Verify the inline checkout handler payload: HMAC(order_id|payment_id, key_secret).
     */
    public function verifyCallback(Order $order, Request $request): void
    {
        $credentials = $this->requireCredentials();

        $paymentId = (string) $request->input('razorpay_payment_id', '');
        $gatewayOrderId = (string) $request->input('razorpay_order_id', '');
        $signature = (string) $request->input('razorpay_signature', '');

        if ($paymentId === '' || $gatewayOrderId === '' || $signature === '') {
            throw new GatewayException('The Razorpay callback is missing parameters.');
        }

        if ($gatewayOrderId !== $order->gateway_order_id) {
            throw new GatewayException('The Razorpay callback references a different order.');
        }

        $expected = hash_hmac('sha256', $gatewayOrderId.'|'.$paymentId, $credentials['key_secret']);

        if (! hash_equals($expected, $signature)) {
            throw new WebhookVerificationException('The Razorpay signature could not be verified.');
        }

        $order->forceFill([
            'gateway_payment_id' => $paymentId,
            'gateway_payload' => array_merge($order->gateway_payload ?? [], [
                'verified_via' => 'callback',
                'razorpay_order_id' => $gatewayOrderId,
            ]),
        ])->save();
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $credentials = $this->gateway->credentials();
        $secret = $credentials['webhook_secret'] ?? '';
        $signature = (string) $request->header('X-Razorpay-Signature', '');

        if ($secret === '' || $signature === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $request->getContent(), $secret), $signature);
    }

    /**
     * Confirm capture against the orders API — the trust root for webhooks.
     */
    public function confirm(Order $order): ?bool
    {
        if ($order->gateway_order_id === null) {
            return null;
        }

        $credentials = $this->gateway->credentials();
        $keyId = $credentials['key_id'] ?? '';
        $keySecret = $credentials['key_secret'] ?? '';

        if ($keyId === '' || $keySecret === '') {
            return null;
        }

        $response = Http::withBasicAuth($keyId, $keySecret)
            ->connectTimeout(5)
            ->timeout(15)
            ->acceptJson()
            ->get(self::ORDERS_ENDPOINT.'/'.$order->gateway_order_id);

        if ($response->failed()) {
            return null;
        }

        return ($response->json('status') ?? '') === 'paid';
    }

    public function isRedirectBased(): bool
    {
        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function requireCredentials(): array
    {
        if (! $this->isConfigured()) {
            throw new GatewayNotConfiguredException(
                'Razorpay is missing key_id/key_secret for the active gateway mode.'
            );
        }

        return $this->gateway->credentials();
    }
}
