<?php

/*
|--------------------------------------------------------------------------
| Salon Information
|--------------------------------------------------------------------------
|
| Static salon data used by the public pages. Contact info will move to
| the settings table and become editable in the admin panel in Phase 3.
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

    'socials' => [
        'instagram' => null,
        'facebook' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Booking Rules
    |--------------------------------------------------------------------------
    |
    | Placeholders until the salon confirms them. These move to the settings
    | table and become editable in the admin panel in Phase 3.
    |
    */
    'booking' => [
        'deposit_amount' => (int) env('BOOKING_DEPOSIT_AMOUNT', 500),
        'deposit_hours' => (int) env('BOOKING_DEPOSIT_HOURS', 48),
        'buffer_minutes' => (int) env('BOOKING_BUFFER_MINUTES', 15),
        'slot_interval_minutes' => (int) env('BOOKING_SLOT_INTERVAL_MINUTES', 30),
        'min_notice_minutes' => (int) env('BOOKING_MIN_NOTICE_MINUTES', 120),
        'max_days_ahead' => (int) env('BOOKING_MAX_DAYS_AHEAD', 60),
    ],

];
