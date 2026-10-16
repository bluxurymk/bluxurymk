<?php

namespace App\Listeners;

use App\Events\BookingWasCancelled;
use App\Events\BookingWasPlaced;
use App\Events\DepositWasConfirmed;
use App\Jobs\SyncBookingToGoogleCalendar;

/**
 * Keeps the salon's Google Calendar in step with booking changes.
 */
class SyncBookingCalendar
{
    public function handle(BookingWasPlaced|DepositWasConfirmed|BookingWasCancelled $event): void
    {
        SyncBookingToGoogleCalendar::dispatchIfEnabled($event->booking);
    }
}
