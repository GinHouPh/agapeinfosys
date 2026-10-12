<?php

namespace App\Services\Payments\Contracts;

use App\Services\Payments\DTOs\CheckoutSession;
use App\Services\Payments\Exceptions\GatewayException;
use Illuminate\Http\Request;

/**
 * A single payment provider implementation.
 *
 * Drivers are resolved from the payment_gateways admin records, so adding a
 * provider is a matter of enabling it and saving credentials.
 */
interface PaymentGatewayDriver
{
    /**
     * Machine name matching payment_gateways.driver.
     */
    public function driver(): string;

    /**
     * Whether the active mode carries the credentials this driver needs.
     */
    public function isConfigured(): bool;

    /**
     * Start a checkout for the payable, returning either an inline checkout
     * payload (browser SDK) or a redirect URL to the provider's hosted page.
     */
    public function createCheckout(Payable $payable): CheckoutSession;

    /**
     * Verify a browser callback for the payable.
     *
     * @throws GatewayException
     */
    public function verifyCallback(Payable $payable, Request $request): void;

    /**
     * Whether an inbound webhook carries a valid provider signature.
     */
    public function verifyWebhookSignature(Request $request): bool;

    /**
     * Ask the provider directly whether the payable has been paid.
     *
     * Returns true/false when the provider has a status endpoint, or null when
     * it does not — in that case trust falls back to signature-verified
     * callbacks and webhooks alone.
     */
    public function confirm(Payable $payable): ?bool;

    /**
     * Whether this driver sends the customer to a hosted payment page instead
     * of opening an inline browser checkout.
     */
    public function isRedirectBased(): bool;
}
