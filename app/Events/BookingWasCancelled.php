<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A booking was cancelled by the customer, by staff, or because its deposit expired.
 */
class BookingWasCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(public Booking $booking) {}
}
