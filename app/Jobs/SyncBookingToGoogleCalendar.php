<?php

namespace App\Jobs;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\GoogleCalendarService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;

/**
 * Brings the booking's Google Calendar event in line with its current state:
 * confirmed bookings get an event, cancelled ones lose it. Reads the booking
 * fresh when it runs, so it is safe to dispatch after every change.
 */
#[DeleteWhenMissingModels]
class SyncBookingToGoogleCalendar implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /**
     * @var list<int>
     */
    public array $backoff = [60, 300, 900, 3600];

    public function __construct(public Booking $booking)
    {
        $this->afterCommit();
    }

    public function uniqueId(): string
    {
        return (string) $this->booking->getKey();
    }

    /**
     * Queue a sync when Google Calendar is configured.
     */
    public static function dispatchIfEnabled(Booking $booking): void
    {
        if (app(GoogleCalendarService::class)->isEnabled()) {
            static::dispatch($booking);
        }
    }

    public function handle(GoogleCalendarService $calendar): void
    {
        if (! $calendar->isEnabled()) {
            return;
        }

        $booking = $this->booking;

        if ($booking->status === BookingStatus::Confirmed) {
            $eventId = $calendar->upsert($booking);

            if ($booking->google_event_id !== $eventId) {
                $booking->forceFill(['google_event_id' => $eventId])->saveQuietly();
            }

            return;
        }

        if ($booking->status === BookingStatus::Cancelled && $booking->google_event_id !== null) {
            $calendar->delete($booking);
            $booking->forceFill(['google_event_id' => null])->saveQuietly();
        }
    }
}
