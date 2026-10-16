<?php

namespace App\Mail;

/**
 * Reminds the customer that the deposit is still unpaid.
 */
class DepositReminder extends BookingMail
{
    protected function subjectLine(): string
    {
        return __('emails.deposit_reminder.subject');
    }

    protected function markdownView(): string
    {
        return 'emails.booking.deposit-reminder';
    }
}
