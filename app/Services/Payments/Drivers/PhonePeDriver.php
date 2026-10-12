<?php

namespace App\Services\Payments\Drivers;

use App\Models\PaymentGateway;
use App\Services\Payments\Contracts\Payable;
use App\Services\Payments\Contracts\PaymentGatewayDriver;
use App\Services\Payments\DTOs\CheckoutSession;
use App\Services\Payments\Exceptions\GatewayException;
use App\Services\Payments\Exceptions\GatewayNotConfiguredException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

/**
 * PhonePe PG Checkout v2 — hosted checkout with checksum-signed requests.
 *
 * Config-ready: add merchant_id + salt in the admin panel to activate.
 * Every completion is confirmed against PhonePe's order status API, so a
 * mis-signed webhook can never mark an order paid.
 */
class PhonePeDriver implements PaymentGatewayDriver
{
    private const SANDBOX_BASE = 'https://api-preprod.phonepe.com/apis/pg';

    private const LIVE_BASE = 'https://api.phonepe.com/apis/pg';

    private const CHECKOUT_PATH = '/pg/v2/checkouts';

    public function __construct(private readonly PaymentGateway $gateway) {}

    public function driver(): string
    {
        return 'phonepe';
    }

    public function isConfigured(): bool
    {
        $credentials = $this->gateway->credentials();

        return ($credentials['merchant_id'] ?? '') !== '' && ($credentials['salt'] ?? '') !== '';
    }

    /**
     * Create a PhonePe checkout session and return its hosted redirect URL.
     */
    public function createCheckout(Payable $payable): CheckoutSession
    {
        $credentials = $this->requireCredentials();

        $payload = [
            'requestId' => (string) $payable->paymentReference(),
            'amount' => $payable->paymentAmount(),
            'merchantOrderId' => (string) $payable->paymentReference(),
            'callbackUrl' => route('checkout.webhook', 'phonepe'),
            'redirectMode' => 'POST',
        ];

        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $encoded = base64_encode($body);
        $checksum = hash('sha256', $encoded.self::CHECKOUT_PATH.$credentials['salt'])
            .'###'.($credentials['salt_index'] ?? '1');

        $response = Http::withHeaders([
            'X-VERIFY' => $checksum,
            'X-MERCHANT-ID' => $credentials['merchant_id'],
            'X-CONTENT-TYPE' => 'application/json',
        ])
            ->connectTimeout(5)
            ->timeout(20)
            ->withBody($body, 'application/json')
            ->post($this->base().self::CHECKOUT_PATH);

        if ($response->failed() || ($response->json('code') ?? '') !== 'SUCCESS') {
            throw new GatewayException(
                'PhonePe rejected the checkout: '.mb_substr($response->body(), 0, 300)
            );
        }

        $redirectUrl = $response->json('data.redirectUrl');

        if (! is_string($redirectUrl) || $redirectUrl === '') {
            throw new GatewayException('PhonePe did not return a redirect URL.');
        }

        $payable->recordGatewayResult(['gateway_order_id' => $response->json('data.paymentId')]);

        return CheckoutSession::redirect('phonepe', $redirectUrl);
    }

    /**
     * Hosted return URLs carry no signature, so the status API is the only
     * trustworthy source — confirm here and reject unless fully paid.
     */
    public function verifyCallback(Payable $payable, Request $request): void
    {
        $merchantOrderId = (string) $request->input('merchantOrderId', $request->input('data.merchantOrderId', ''));

        if ($merchantOrderId !== '' && $merchantOrderId !== $payable->paymentReference()) {
            throw new GatewayException('The PhonePe callback references a different order.');
        }

        if ($this->confirm($payable) !== true) {
            throw new GatewayException('PhonePe has not confirmed this payment yet.');
        }

        $payable->recordGatewayResult([], ['verified_via' => 'status_api']);
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $credentials = $this->gateway->credentials();
        $salt = $credentials['salt'] ?? '';

        if ($salt === '') {
            return false;
        }

        $signature = (string) $request->header('X-SIGNATURE', '');

        if ($signature === '') {
            return false;
        }

        $payload = base64_encode($request->getContent());

        $expected = hash('sha256', $payload.$request->path().$salt)
            .'###'.($credentials['salt_index'] ?? '1');

        return hash_equals($expected, $signature);
    }

    /**
     * Ask PhonePe for the authoritative order state.
     */
    public function confirm(Payable $payable): ?bool
    {
        $credentials = $this->gateway->credentials();
        $salt = $credentials['salt'] ?? '';

        if ($salt === '') {
            return null;
        }

        $path = '/pg/v2/order/'.$payable->paymentReference().'/status';
        $checksum = hash('sha256', $path.$salt).'###'.($credentials['salt_index'] ?? '1');

        $response = Http::withHeaders([
            'X-VERIFY' => $checksum,
            'X-MERCHANT-ID' => $credentials['merchant_id'] ?? '',
        ])
            ->connectTimeout(5)
            ->timeout(15)
            ->get($this->base().$path);

        if ($response->failed() || ($response->json('code') ?? '') !== 'SUCCESS') {
            return null;
        }

        return ($response->json('data.state') ?? '') === 'COMPLETED';
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
                'PhonePe is missing merchant_id/salt for the active gateway mode.'
            );
        }

        return Arr::only($this->gateway->credentials(), ['merchant_id', 'salt', 'salt_index']);
    }
}
