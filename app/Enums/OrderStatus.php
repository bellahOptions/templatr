<?php

namespace App\Enums;

/**
 * Lifecycle of an order from creation to fulfilment.
 *
 * Stored as the existing string column values, so no data migration is needed.
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case AwaitingApproval = 'awaiting_approval';
    case Processing = 'processing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::AwaitingApproval => 'Awaiting approval',
            self::Processing => 'Processing',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::Failed => 'Failed',
        };
    }

    /**
     * Statuses an order may still move to once it is in this state.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::AwaitingApproval, self::Processing, self::Completed, self::Cancelled, self::Failed],
            self::AwaitingApproval => [self::Processing, self::Completed, self::Cancelled, self::Failed],
            self::Processing => [self::Completed, self::Cancelled, self::Failed],
            self::Completed, self::Cancelled => [],
            self::Failed => [self::Processing, self::Cancelled],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
