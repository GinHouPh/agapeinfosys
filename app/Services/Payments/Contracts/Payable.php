<?php

namespace App\Services\Payments\Contracts;

use App\Services\Payments\DTOs\SubscriptionTerms;

/**
 * Anything the payment drivers can charge through a provider.
 *
 * A single-plan Order and a multi-item ShopOrder both implement this, so
 * drivers only ever see payment-facing data — never the domain model.
 */
interface Payable
{
    /**
     * Unique provider-side order number, e.g. "AGP-7F3K2Q".
     */
    public function paymentReference(): string;

    /**
     * Charge amount in minor units.
     */
    public function paymentAmount(): int;

    public function paymentCurrency(): string;

    /**
     * Amount rendered for humans, e.g. "₹4,999.00".
     */
    public function paymentAmountDisplay(): string;

    /**
     * Short description shown on the provider's payment page.
     */
    public function paymentDescription(): string;

    /**
     * Brand shown on hosted checkout pages (product or shop).
     */
    public function paymentBrand(): string;

    /**
     * Where the provider sends the customer after payment.
     */
    public function paymentReturnUrl(): string;

    /**
     * Source context recorded with the provider, e.g. a product slug.
     */
    public function paymentContext(): string;

    public function paymentSubscriberKey(): ?string;

    public function paymentName(): ?string;

    public function paymentEmail(): ?string;

    public function paymentPhone(): ?string;

    /**
     * Recurring terms when this payable must be charged through a mandate
     * (subscription) API, or null for a plain one-time payment.
     */
    public function paymentSubscriptionTerms(): ?SubscriptionTerms;

    /**
     * Provider payload stored so far, for merging fresh data into.
     *
     * @return array<string, mixed>
     */
    public function paymentGatewayPayload(): array;

    /**
     * Provider-side order id, when one has already been recorded.
     */
    public function gatewayOrderId(): ?string;

    /**
     * Persist provider ids and freshly verified payload in one write.
     *
     * @param  array<string, mixed>  $attributes  gateway_order_id, gateway_payment_id, ...
     * @param  array<string, mixed>  $payload  merged into the stored provider payload
     */
    public function recordGatewayResult(array $attributes, array $payload = []): void;
}
