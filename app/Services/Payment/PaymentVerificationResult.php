<?php

namespace App\Services\Payment;

use App\Models\Order;

/**
 * Immutable outcome of validating a provider transaction against an order.
 *
 * Every check that must pass before an order can be marked paid is evaluated
 * here so the decision is made in exactly one place, with a machine-readable
 * reason for logging and for the webhook response body.
 */
class PaymentVerificationResult
{
    /**
     * @param  array<string, mixed>  $details  verified transaction details (amount, currency, reference, ...)
     */
    public function __construct(
        public readonly bool $verified,
        public readonly string $reason,
        public readonly ?Order $order = null,
        public readonly array $details = [],
    ) {}

    public static function verified(Order $order, array $details = []): self
    {
        return new self(true, 'verified', $order, $details);
    }

    public static function rejected(string $reason, ?Order $order = null, array $details = []): self
    {
        return new self(false, $reason, $order, $details);
    }

    public function toArray(): array
    {
        return [
            'verified' => $this->verified,
            'reason' => $this->reason,
            'order_id' => $this->order?->id,
            'order_number' => $this->order?->order_number,
            'details' => $this->details,
        ];
    }
}
