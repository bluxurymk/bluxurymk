<?php

namespace App\Listeners;

use App\Enums\BookingStatus;
use App\Events\BookingWasCancelled;
use App\Events\BookingWasPlaced;
use App\Events\DepositWasConfirmed;
use App\Mail\AdminNewBooking;
use App\Mail\BookingCancelled;
use App\Mail\BookingConfirmed;
use App\Mail\BookingMail;
use App\Mail\BookingReceived;
use App\Models\Booking;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the customer and salon emails for booking lifecycle events.
 * Customers without an email address (phone bookings) are skipped.
 */
class SendBookingEmails
{
    public function handleBookingWasPlaced(BookingWasPlaced $event): void
    {
        $booking = $event->booking;

        $this->sendToCustomer($booking, $booking->status === BookingStatus::Confirmed
            ? new BookingConfirmed($booking)
            : new BookingReceived($booking));

        if ($booking->customer_email) {
            $booking->forceFill(['received_sent_at' => now()])->saveQuietly();
        }

        if ($event->placedByCustomer) {
            Mail::to(config('salon.email'))->send(new AdminNewBooking($booking));
        }
    }

    public function handleDepositWasConfirmed(DepositWasConfirmed $event): void
    {
        $this->sendToCustomer($event->booking, new BookingConfirmed($event->booking));
    }

    public function handleBookingWasCancelled(BookingWasCancelled $event): void
    {
        $this->sendToCustomer($event->booking, new BookingCancelled($event->booking));
    }

    private function sendToCustomer(Booking $booking, BookingMail $mail): void
    {
        if (blank($booking->customer_email)) {
            return;
        }

        Mail::to($booking->customer_email, $booking->customer_name)->send($mail);
    }
}
