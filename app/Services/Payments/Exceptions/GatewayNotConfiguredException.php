<?php

namespace App\Services\Payments\Exceptions;

/**
 * A driver was asked to run without credentials in the active mode.
 */
class GatewayNotConfiguredException extends GatewayException {}
