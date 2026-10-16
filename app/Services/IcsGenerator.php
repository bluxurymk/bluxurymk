<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\CarbonImmutable;

/**
 * Writes iCalendar (RFC 5545) files: a single-event attachment for the
 * customer and the salon's private subscription feed.
 *
 * @phpstan-import-type CalendarEvent from BookingCalendarEvents
 */
class IcsGenerator
{
    public function __construct(private BookingCalendarEvents $events) {}

    public function forCustomer(Booking $booking): string
    {
        return $this->calendar([$this->events->forCustomer($booking)]);
    }

    /**
     * @param  iterable<Booking>  $bookings
     */
    public function salonFeed(iterable $bookings): string
    {
        $events = [];

        foreach ($bookings as $booking) {
            $events[] = $this->events->forSalon($booking);
        }

        return $this->calendar($events, config('salon.name'));
    }

    /**
     * @param  list<CalendarEvent>  $events
     */
    private function calendar(array $events, ?string $name = null): string
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';
        $now = $this->utc(now()->toImmutable());

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//'.config('salon.name').'//Bookings//MK',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
        ];

        if ($name !== null) {
            $lines[] = 'X-WR-CALNAME:'.$this->escape($name);
            $lines[] = 'X-WR-TIMEZONE:'.config('app.timezone');
            $lines[] = 'REFRESH-INTERVAL;VALUE=DURATION:PT1H';
            $lines[] = 'X-PUBLISHED-TTL:PT1H';
        }

        foreach ($events as $event) {
            array_push($lines,
                'BEGIN:VEVENT',
                'UID:'.$event['uid'].'@'.$host,
                'DTSTAMP:'.$now,
                'DTSTART:'.$this->utc($event['starts_at']),
                'DTEND:'.$this->utc($event['ends_at']),
                'SUMMARY:'.$this->escape($event['summary']),
                'DESCRIPTION:'.$this->escape($event['description']),
                'LOCATION:'.$this->escape($event['location']),
                'END:VEVENT',
            );
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", array_map($this->fold(...), $lines))."\r\n";
    }

    private function utc(CarbonImmutable $date): string
    {
        return $date->utc()->format('Ymd\THis\Z');
    }

    private function escape(string $text): string
    {
        return str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\\;', '\\,', '\\n', '\\n'], $text);
    }

    /**
     * Fold lines longer than 75 octets, never splitting a multibyte character.
     */
    private function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }

        $folded = '';
        $current = '';

        foreach (mb_str_split($line) as $character) {
            $limit = $folded === '' ? 75 : 74;

            if (strlen($current.$character) > $limit) {
                $folded .= ($folded === '' ? '' : "\r\n ").$current;
                $current = '';
            }

            $current .= $character;
        }

        return $folded.($folded === '' ? '' : "\r\n ").$current;
    }
}
