<?php

namespace App\Services\Payments\Drivers;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\Payments\Contracts\PaymentGatewayDriver;
use App\Services\Payments\DTOs\CheckoutSession;
use App\Services\Payments\Exceptions\GatewayException;
use App\Services\Payments\Exceptions\GatewayNotConfiguredException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Cashfree PG — hosted checkout via payment links.
 *
 * Config-ready: add client_id + client_secret in the admin panel to activate.
 * Webhooks are signed with HMAC-SHA256 over "timestamp.rawBody" and every
 * completion is confirmed against the Cashfree order status API.
 */
class CashfreeDriver implements PaymentGatewayDriver
{
    private const SANDBOX_BASE = 'https://api-sandbox.cashfree.com/pg';

    private const LIVE_BASE = 'https://api.cashfree.com/pg';

    private const API_VERSION = '2023-08-01';

    public function __construct(private readonly PaymentGateway $gateway) {}

    public function driver(): string
    {
        return 'cashfree';
    }

    public function isConfigured(): bool
    {
        $credentials = $this->gateway->credentials();

        return ($credentials['client_id'] ?? '') !== '' && ($credentials['client_secret'] ?? '') !== '';
    }

    /**
     * Create a Cashfree order and return its hosted payment link.
     */
    public function createCheckout(Order $order): CheckoutSession
    {
        $credentials = $this->requireCredentials();

        $response = Http::withHeaders([
            'x-client-id' => $credentials['client_id'],
            'x-client-secret' => $credentials['client_secret'],
            'x-api-version' => self::API_VERSION,
        ])
            ->connectTimeout(5)
            ->timeout(20)
            ->acceptJson()
            ->post($this->base().'/orders', [
                'order_id' => $order->reference,
                'order_amount' => number_format($order->amount / 100, 2, '.', ''),
                'order_currency' => $order->currency,
                'customer_details' => [[
                    'customer_id' => substr($order->subscriber_key, 0, 60),
                    'customer_email' => $order->email ?? 'no-reply@example.com',
                    'customer_phone' => $order->phone ?? '9999999999',
                ]],
                'return_url' => route('checkout.result', [$order->product, $order]),
                'notify_url' => route('checkout.webhook', 'cashfree'),
            ]);

        if ($response->failed()) {
            throw new GatewayException(
                'Cashfree rejected the order: '.mb_substr($response->body(), 0, 300)
            );
        }

        $paymentLink = $response->json('payment_link');

        if (! is_string($paymentLink) || $paymentLink === '') {
            throw new GatewayException('Cashfree did not return a payment link.');
        }

        $order->forceFill(['gateway_order_id' => (string) $response->json('cf_order_id')])->save();

        return CheckoutSession::redirect('cashfree', $paymentLink);
    }

    /**
     * Hosted return URLs carry no signature, so the status API is the only
     * trustworthy source — confirm here and reject unless fully paid.
     */
    public function verifyCallback(Order $order, Request $request): void
    {
        $returnedOrderId = (string) $request->query('order_id', $request->input('order_id', ''));

        if ($returnedOrderId !== '' && $returnedOrderId !== $order->reference) {
            throw new GatewayException('The Cashfree callback references a different order.');
        }

        if ($this->confirm($order) !== true) {
            throw new GatewayException('Cashfree has not confirmed this payment yet.');
        }

        $order->forceFill([
            'gateway_payload' => array_merge($order->gateway_payload ?? [], [
                'verified_via' => 'status_api',
            ]),
        ])->save();
    }

    /**
     * Cashfree signs "timestamp.rawBody" with the client secret.
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $credentials = $this->gateway->credentials();
        $secret = $credentials['client_secret'] ?? '';
        $signature = (string) $request->header('x-webhook-signature', '');
        $timestamp = (string) $request->header('x-webhook-timestamp', '');

        if ($secret === '' || $signature === '' || $timestamp === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Ask Cashfree for the authoritative order state.
     */
    public function confirm(Order $order): ?bool
    {
        $credentials = $this->gateway->credentials();
        $clientId = $credentials['client_id'] ?? '';
        $clientSecret = $credentials['client_secret'] ?? '';

        if ($clientId === '' || $clientSecret === '') {
            return null;
        }

        $response = Http::withHeaders([
            'x-client-id' => $clientId,
            'x-client-secret' => $clientSecret,
            'x-api-version' => self::API_VERSION,
        ])
            ->connectTimeout(5)
            ->timeout(15)
            ->get($this->base().'/orders/'.$order->reference);

        if ($response->failed()) {
            return null;
        }

        return ($response->json('order_status') ?? '') === 'PAID';
    }

    public function isRedirectBased(): bool
    {
        return true;
    }

    private function base(): string
    {
        return $this->gateway->isLive() ? self::LIVE_BASE : self::SANDBOX_BASE;
    }

    /**
     * @return array<string, mixed>
     */
    private function requireCredentials(): array
    {
        if (! $this->isConfigured()) {
            throw new GatewayNotConfiguredException(
                'Cashfree is missing client_id/client_secret for the active gateway mode.'
            );
        }

        return $this->gateway->credentials();
    }
}
