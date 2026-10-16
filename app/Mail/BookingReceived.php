<?php

namespace App\Mail;

/**
 * A new booking is waiting for its cash deposit.
 */
class BookingReceived extends BookingMail
{
    protected bool $includesCalendarEvent = true;

    protected function subjectLine(): string
    {
        return __('emails.received.subject');
    }

    protected function markdownView(): string
    {
        return 'emails.booking.received';
    }
}
