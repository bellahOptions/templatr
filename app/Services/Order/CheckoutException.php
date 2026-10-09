<?php

namespace App\Services\Order;

use App\Models\Product;
use RuntimeException;

/**
 * Raised when a cart or checkout request cannot be honoured safely.
 */
class CheckoutException extends RuntimeException {}
