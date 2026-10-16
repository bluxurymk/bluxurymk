<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Tells the salon about a new online booking. Replies go to the customer.
 */
class AdminNewBooking extends BookingMail
{
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine(),
            replyTo: $this->booking->customer_email
                ? [new Address($this->booking->customer_email, $this->booking->customer_name)]
                : [],
        );
    }

    protected function subjectLine(): string
    {
        return __('emails.admin_new.subject', [
            'name' => $this->booking->customer_name,
            'when' => $this->booking->starts_at->format('d.m. H:i'),
        ]);
    }

    protected function markdownView(): string
    {
        return 'emails.booking.admin-new';
    }
}
