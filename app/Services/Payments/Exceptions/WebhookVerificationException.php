<?php

namespace App\Services\Payments\Exceptions;

/**
 * A webhook or callback failed signature verification.
 */
class WebhookVerificationException extends GatewayException {}
