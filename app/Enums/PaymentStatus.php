<?php

namespace App\Enums;

/**
 * Payment state of an order.
 *
 * Only a verified provider transaction (or an explicit admin approval of an
 * offline payment) may move an order to `Paid`.
 */
enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Pending = 'pending';
    case Paid = 'paid';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Unpaid',
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Refunded => 'Refunded',
        };
    }

    /**
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Unpaid => [self::Pending, self::Paid, self::Refunded],
            self::Pending => [self::Paid, self::Unpaid, self::Refunded],
            self::Paid => [self::Refunded],
            self::Refunded => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
