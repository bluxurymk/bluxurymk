<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PendingDeposit = 'pending_deposit';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case NoShow = 'no_show';

    /**
     * Statuses that occupy a time slot in the calendar.
     *
     * @return list<self>
     */
    public static function blocking(): array
    {
        return [self::PendingDeposit, self::Confirmed];
    }

    public function blocksSlot(): bool
    {
        return in_array($this, self::blocking(), true);
    }

    public function label(): string
    {
        return __('booking.status.'.$this->value);
    }
}
