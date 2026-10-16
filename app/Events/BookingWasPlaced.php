<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A new booking was made online by a customer or entered by staff.
 */
class BookingWasPlaced
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public bool $placedByCustomer,
    ) {}
}
