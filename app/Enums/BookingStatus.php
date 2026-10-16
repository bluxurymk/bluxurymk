<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BookingStatus: string implements HasColor, HasLabel
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

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PendingDeposit => 'warning',
            self::Confirmed => 'success',
            self::Cancelled => 'gray',
            self::Completed => 'info',
            self::NoShow => 'danger',
        };
    }
}
