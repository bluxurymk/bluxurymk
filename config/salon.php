<?php

/*
|--------------------------------------------------------------------------
| Salon Information
|--------------------------------------------------------------------------
|
| Defaults for the salon's contact info and booking rules. The admin can
| override them on the Settings page (see App\Services\SalonSettings).
| Services live in the database (see ServiceSeeder).
|
*/

return [

    'name' => 'B Luxury Hair Salon',

    'address' => 'ул. Димитар Влахов',

    'city' => 'Куманово',

    'phone' => '+389 78 906 112',

    'email' => 'boban_hair@yahoo.com',

    'map_query' => 'Dimitar Vlahov, Kumanovo, North Macedonia',

    /*
    | Secret part of the private iCal feed URL (/kalendar/{secret}.ics) the
    | salon subscribes to on their phones. Empty disables the feed.
    */
    'calendar_feed_secret' => env('CALENDAR_FEED_SECRET'),

    'socials' => [
        'instagram' => null,
        'facebook' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Booking Rules
    |--------------------------------------------------------------------------
    |
    | Defaults. The admin can override them on the Settings page (see
    | App\Services\SalonSettings).
    |
    */
    'booking' => [
        'deposit_amount' => (int) env('BOOKING_DEPOSIT_AMOUNT', 500),
        'deposit_hours' => (int) env('BOOKING_DEPOSIT_HOURS', 48),
        'reminder_hours' => (int) env('BOOKING_REMINDER_HOURS', 24),
        'buffer_minutes' => (int) env('BOOKING_BUFFER_MINUTES', 15),
        'slot_interval_minutes' => (int) env('BOOKING_SLOT_INTERVAL_MINUTES', 30),
        'min_notice_minutes' => (int) env('BOOKING_MIN_NOTICE_MINUTES', 120),
        'max_days_ahead' => (int) env('BOOKING_MAX_DAYS_AHEAD', 60),
    ],

];
