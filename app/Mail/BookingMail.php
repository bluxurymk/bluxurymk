<?php

namespace App\Mail;

use App\Models\Booking;
use App\Services\BookingCalendarEvents;
use App\Services\IcsGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Base for every booking email: queued after the surrounding transaction
 * commits, rendered with the salon's black and gold Markdown theme.
 */
abstract class BookingMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $theme = 'bluxury';

    /**
     * Whether the email carries the appointment as an .ics attachment and a Google Calendar link.
     */
    protected bool $includesCalendarEvent = false;

    public function __construct(public Booking $booking)
    {
        $this->afterCommit();
    }

    abstract protected function subjectLine(): string;

    abstract protected function markdownView(): string;

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine(),
            replyTo: [new Address(config('salon.email'), config('salon.name'))],
        );
    }

    public function content(): Content
    {
        $this->booking->loadMissing(['service', 'stylist']);

        return new Content(
            markdown: $this->markdownView(),
            with: [
                'booking' => $this->booking,
                'when' => $this->formatDate($this->booking->starts_at),
                'deadline' => $this->booking->deposit_due_at ? $this->formatDate($this->booking->deposit_due_at) : null,
                'amount' => number_format($this->booking->deposit_amount, 0, ',', '.'),
                'manageUrl' => route('booking.show', $this->booking),
                'googleCalendarUrl' => $this->includesCalendarEvent
                    ? app(BookingCalendarEvents::class)->googleCalendarUrl($this->booking)
                    : null,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->includesCalendarEvent) {
            return [];
        }

        return [
            Attachment::fromData(fn (): string => app(IcsGenerator::class)->forCustomer($this->booking->loadMissing(['service', 'stylist'])), 'termin.ics')
                ->withMime('text/calendar; charset=utf-8; method=PUBLISH'),
        ];
    }

    protected function formatDate(\DateTimeInterface $date): string
    {
        return CarbonImmutable::instance($date)->locale(app()->getLocale())->isoFormat('dddd, D MMMM YYYY, HH:mm');
    }
}
