<?php

namespace App\Mail;

/**
 * Reminds the customer of an upcoming appointment.
 */
class BookingReminder extends BookingMail
{
    protected function subjectLine(): string
    {
        return __('emails.reminder.subject');
    }

    protected function markdownView(): string
    {
        return 'emails.booking.reminder';
    }
}
