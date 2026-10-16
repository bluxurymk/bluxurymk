<?php

namespace App\Services;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Carbon\CarbonImmutable;

/**
 * Builds the calendar event for a booking, as the customer sees it
 * (email attachment, "Add to Google Calendar") or as the salon sees it
 * (iCal feed, Google Calendar sync).
 *
 * @phpstan-type CalendarEvent array{uid: string, summary: string, description: string, location: string, starts_at: CarbonImmutable, ends_at: CarbonImmutable}
 */
class BookingCalendarEvents
{
    /**
     * @return CalendarEvent
     */
    public function forCustomer(Booking $booking): array
    {
        return [
            'uid' => $this->uid($booking),
            'summary' => $booking->service->name.' — '.config('salon.name'),
            'description' => implode("\n", array_filter([
                $booking->stylist ? __('emails.labels.stylist').': '.$booking->stylist->name : null,
                __('emails.labels.phone').': '.config('salon.phone'),
                route('booking.show', $booking),
            ])),
            'location' => $this->location(),
            'starts_at' => $booking->starts_at,
            'ends_at' => $booking->ends_at,
        ];
    }

    /**
     * @return CalendarEvent
     */
    public function forSalon(Booking $booking): array
    {
        return [
            'uid' => $this->uid($booking),
            'summary' => $booking->service->name.' — '.$booking->customer_name,
            'description' => implode("\n", array_filter([
                $booking->stylist ? __('emails.labels.stylist').': '.$booking->stylist->name : null,
                __('emails.labels.phone').': '.$booking->customer_phone,
                $booking->customer_email ? __('emails.labels.email').': '.$booking->customer_email : null,
                $booking->note ? __('emails.labels.note').': '.$booking->note : null,
                BookingResource::getUrl('edit', ['record' => $booking], panel: 'admin'),
            ])),
            'location' => $this->location(),
            'starts_at' => $booking->starts_at,
            'ends_at' => $booking->ends_at,
        ];
    }

    /**
     * Google Calendar "add event" link for the customer.
     */
    public function googleCalendarUrl(Booking $booking): string
    {
        $event = $this->forCustomer($booking);

        return 'https://calendar.google.com/calendar/render?'.http_build_query([
            'action' => 'TEMPLATE',
            'text' => $event['summary'],
            'dates' => $event['starts_at']->utc()->format('Ymd\THis\Z').'/'.$event['ends_at']->utc()->format('Ymd\THis\Z'),
            'details' => $event['description'],
            'location' => $event['location'],
            'ctz' => config('app.timezone'),
        ], '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Stable identifier, also used as the Google Calendar event id, which
     * only allows the base32hex characters 0-9 and a-v.
     */
    public function uid(Booking $booking): string
    {
        return sprintf('bl%010d', $booking->getKey());
    }

    private function location(): string
    {
        return config('salon.name').', '.config('salon.address').', '.config('salon.city');
    }
}
