<?php

use App\Mail\BookingCancelled;
use App\Mail\BookingConfirmed;
use App\Mail\BookingReceived;
use App\Models\Booking;
use App\Models\Service;
use App\Services\IcsGenerator;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-19 08:00'));
});

test('the customer calendar file holds the appointment in UTC with escaped text', function () {
    $service = Service::factory()->create(['name' => 'Балајаж, долга коса']);
    $booking = Booking::factory()->for($service)->at('2026-10-22 10:00', 180)->create();

    $ics = app(IcsGenerator::class)->forCustomer($booking);

    expect($ics)
        ->toStartWith("BEGIN:VCALENDAR\r\n")
        ->toEndWith("END:VCALENDAR\r\n")
        ->toContain("DTSTART:20261022T080000Z\r\n")
        ->toContain("DTEND:20261022T110000Z\r\n")
        ->toContain('SUMMARY:Балајаж\, долга коса — B Luxury Hair Salon')
        ->toContain('UID:bl'.sprintf('%010d', $booking->id).'@');

    foreach (explode("\r\n", $ics) as $line) {
        expect(strlen($line))->toBeLessThanOrEqual(75);
    }
});

test('received and confirmed emails carry the calendar file and a Google Calendar link', function (string $mailClass) {
    $booking = Booking::factory()->at('2026-10-22 10:00')->create();

    $mail = new $mailClass($booking);
    $attachment = $mail->attachments()[0];

    expect($mail->attachments())->toHaveCount(1)
        ->and($attachment->as)->toBe('termin.ics')
        ->and($attachment->mime)->toStartWith('text/calendar');

    $mail->assertSeeInHtml('https://calendar.google.com/calendar/render?action=TEMPLATE')
        ->assertSeeInHtml('dates=20261022T080000Z%2F20261022T090000Z');
})->with([
    'received' => [BookingReceived::class],
    'confirmed' => [BookingConfirmed::class],
]);

test('the cancellation email has no calendar file', function () {
    $booking = Booking::factory()->at('2026-10-22 10:00')->cancelled()->create();

    expect((new BookingCancelled($booking))->attachments())->toBe([]);
});

test('the private feed lists recent and upcoming confirmed bookings', function () {
    config(['salon.calendar_feed_secret' => 'feed-secret']);
    $confirmed = Booking::factory()->at('2026-10-22 10:00')->confirmed()->create(['customer_name' => 'Потврдена Клиентка']);
    Booking::factory()->at('2026-10-22 12:00')->create(['customer_name' => 'Чека Капар']);
    Booking::factory()->at('2026-10-22 14:00')->cancelled()->create(['customer_name' => 'Откажана Клиентка']);
    Booking::factory()->at('2026-08-01 10:00')->confirmed()->create(['customer_name' => 'Стара Клиентка']);

    $response = $this->get(route('calendar.feed', 'feed-secret'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/calendar; charset=utf-8');

    expect($response->getContent())
        ->toContain('Потврдена Клиентка')
        ->toContain($confirmed->customer_phone)
        ->toContain('X-WR-CALNAME:B Luxury Hair Salon')
        ->not->toContain('Чека Капар')
        ->not->toContain('Откажана Клиентка')
        ->not->toContain('Стара Клиентка');
});

test('the private feed is hidden without the right secret', function (?string $configuredSecret, string $requestedSecret) {
    config(['salon.calendar_feed_secret' => $configuredSecret]);

    $this->get("/kalendar/{$requestedSecret}.ics")->assertNotFound();
})->with([
    'feed disabled' => [null, 'anything'],
    'wrong secret' => ['feed-secret', 'guess'],
]);
