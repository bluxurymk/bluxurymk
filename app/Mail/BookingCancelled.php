<?php

namespace App\Mail;

/**
 * Tells the customer their booking was cancelled, and why.
 */
class BookingCancelled extends BookingMail
{
    protected function subjectLine(): string
    {
        return __('emails.cancelled.subject');
    }

    protected function markdownView(): string
    {
        return 'emails.booking.cancelled';
    }
}
