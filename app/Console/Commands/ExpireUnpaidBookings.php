<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\SalonSettings;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('bookings:expire-unpaid')]
#[Description('Cancel bookings whose cash deposit was not paid before the deadline')]
class ExpireUnpaidBookings extends Command
{
    public function handle(SalonSettings $settings): int
    {
        $settings->applyToConfig();

        $expired = 0;

        Booking::query()
            ->where('status', BookingStatus::PendingDeposit)
            ->where('deposit_due_at', '<=', now())
            ->lazyById()
            ->each(function (Booking $booking) use (&$expired): void {
                if ($booking->expireUnpaidDeposit()) {
                    $expired++;
                }
            });

        $this->info("Expired {$expired} unpaid booking(s).");

        return self::SUCCESS;
    }
}
