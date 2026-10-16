<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Staff received the cash deposit and the booking is confirmed.
 */
class DepositWasConfirmed
{
    use Dispatchable, SerializesModels;

    public function __construct(public Booking $booking) {}
}
