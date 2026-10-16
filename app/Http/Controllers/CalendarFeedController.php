<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\IcsGenerator;
use Illuminate\Http\Response;

/**
 * Private iCal feed of confirmed bookings that the salon subscribes to on
 * their phones (Apple Calendar or any other calendar app).
 */
class CalendarFeedController extends Controller
{
    /**
     * How far back past appointments stay in the feed.
     */
    private const DAYS_OF_HISTORY = 30;

    public function __invoke(string $secret, IcsGenerator $ics): Response
    {
        $expectedSecret = (string) config('salon.calendar_feed_secret');

        abort_if($expectedSecret === '' || ! hash_equals($expectedSecret, $secret), 404);

        $bookings = Booking::query()
            ->with(['service', 'stylist'])
            ->where('status', BookingStatus::Confirmed)
            ->where('starts_at', '>=', now()->subDays(self::DAYS_OF_HISTORY))
            ->orderBy('starts_at')
            ->get();

        return response($ics->salonFeed($bookings), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="bluxury.ics"',
            'Cache-Control' => 'private, max-age=300',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
