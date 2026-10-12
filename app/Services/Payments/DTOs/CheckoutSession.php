<?php

namespace App\Services\Payments\DTOs;

/**
 * Everything the checkout view or controller needs to hand off to a provider.
 *
 * Inline sessions open a browser SDK checkout; redirect sessions send the
 * customer to a hosted page (either via a plain URL or an auto-submitted
 * form for providers that require a signed form POST).
 */
final class CheckoutSession
{
    public const MODE_INLINE = 'inline';

    public const MODE_REDIRECT = 'redirect';

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $formFields
     */
    private function __construct(
        public readonly string $driver,
        public readonly string $mode,
        public readonly ?string $redirectUrl,
        public readonly array $payload,
        public readonly array $formFields = [],
    ) {}

    /**
     * Browser SDK checkout: render the payload into the payment view.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function inline(string $driver, array $payload): self
    {
        return new self($driver, self::MODE_INLINE, null, $payload);
    }

    /**
     * Hosted checkout reached with a plain redirect.
     */
    public static function redirect(string $driver, string $url): self
    {
        return new self($driver, self::MODE_REDIRECT, $url, []);
    }

    /**
     * Hosted checkout that requires a signed form POST (e.g. PayU).
     *
     * @param  array<string, string>  $fields
     */
    public static function redirectForm(string $driver, string $url, array $fields): self
    {
        return new self($driver, self::MODE_REDIRECT, $url, [], $fields);
    }

    public function isInline(): bool
    {
        return $this->mode === self::MODE_INLINE;
    }

    /**
     * Whether the hosted redirect must be delivered as a form POST.
     */
    public function hasForm(): bool
    {
        return $this->formFields !== [];
    }
}
