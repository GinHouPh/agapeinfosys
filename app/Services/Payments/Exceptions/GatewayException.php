<?php

namespace App\Services\Payments\Exceptions;

use RuntimeException;

/**
 * Any failure raised by a payment provider driver.
 */
class GatewayException extends RuntimeException {}
