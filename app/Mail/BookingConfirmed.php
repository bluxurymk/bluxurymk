<?php

namespace App\Mail;

/**
 * The deposit was received and the booking is confirmed.
 */
class BookingConfirmed extends BookingMail
{
    protected function subjectLine(): string
    {
        return __('emails.confirmed.subject');
    }

    protected function markdownView(): string
    {
        return 'emails.booking.confirmed';
    }
}
