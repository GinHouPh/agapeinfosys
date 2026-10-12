<?php

namespace App\Services\Payments;

use App\Services\Payments\Exceptions\GatewayException;

/**
 * Resolves the subscriber identity an entitlement is granted to.
 */
final class CustomerKey
{
    /**
     * The account id when given, otherwise the email — a checkout without
     * either has no one to grant access to.
     *
     * @param  array<string, string|null>  $customer
     *
     * @throws GatewayException
     */
    public static function resolve(array $customer): string
    {
        $key = (string) ($customer['subscriber_key'] ?? '');

        if ($key === '') {
            $key = (string) ($customer['email'] ?? '');
        }

        if ($key === '') {
            throw new GatewayException('A subscriber key or email is required to checkout.');
        }

        return $key;
    }
}
