<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Stylist;
use App\Models\WorkingHour;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

beforeEach(function () {
    config([
        'salon.booking.deposit_amount' => 500,
        'salon.booking.deposit_hours' => 48,
        'salon.booking.buffer_minutes' => 15,
        'salon.booking.slot_interval_minutes' => 30,
        'salon.booking.min_notice_minutes' => 120,
        'salon.booking.max_days_ahead' => 60,
    ]);

    // Monday 2026-10-19, 08:00. The salon works Tuesdays 09:00–17:00 in these tests.
    $this->travelTo(CarbonImmutable::parse('2026-10-19 08:00'));
});

/**
 * A 60-minute service with the given number of stylists, bookable on Tuesdays.
 *
 * @return array{0: Service, 1: Collection<int, Stylist>}
 */
function bookableService(int $stylistCount = 1): array
{
    $service = Service::factory()->lasting(60)->create();
    $stylists = Stylist::factory()->count($stylistCount)->hasAttached($service)->create();
    WorkingHour::factory()->on(2, '09:00', '17:00')->create();

    return [$service, $stylists];
}

/**
 * @return array<string, mixed>
 */
function bookingPayload(Service $service, array $overrides = []): array
{
    return [
        'service' => $service->slug,
        'stylist' => null,
        'date' => '2026-10-20',
        'time' => '10:00',
        'customer_name' => 'Ана Петровска',
        'customer_phone' => '070 123 456',
        'customer_email' => 'ana@example.com',
        'note' => 'Прв пат доаѓам.',
        'consent' => '1',
        ...$overrides,
    ];
}

test('the booking page lists the services and the stylists who perform them', function () {
    [$service, $stylists] = bookableService(2);

    $this->get(route('booking.create', ['service' => $service->slug]))
        ->assertOk()
        ->assertSee($service->name)
        ->assertSee($stylists[1]->name)
        ->assertSee('name="customer_name"', escape: false)
        ->assertSee('500 ден.');
});

test('the free days of a month are listed', function () {
    [$service] = bookableService(1);

    $this->getJson(route('booking.dates', ['service' => $service->slug, 'month' => '2026-10']))
        ->assertOk()
        ->assertExactJson(['dates' => ['2026-10-20', '2026-10-27']]);
});

test('the free times of a day are listed', function () {
    [$service] = bookableService(1);

    $slots = $this->getJson(route('booking.slots', ['service' => $service->slug, 'date' => '2026-10-20']))
        ->assertOk()
        ->json('slots');

    expect($slots)->toContain('09:00', '16:00')->not->toContain('16:30');
});

test('the free times follow the chosen stylist', function () {
    [$service, $stylists] = bookableService(2);
    Booking::factory()->for($stylists[0])->for($service)->at('2026-10-20 10:00')->create();

    $slotsFor = fn (?Stylist $stylist): array => $this->getJson(route('booking.slots', [
        'service' => $service->slug,
        'stylist' => $stylist?->id,
        'date' => '2026-10-20',
    ]))->json('slots');

    expect($slotsFor($stylists[0]))->not->toContain('10:00')
        ->and($slotsFor($stylists[1]))->toContain('10:00')
        ->and($slotsFor(null))->toContain('10:00');
});

test('availability requests need a known service and a valid month or date', function (array $query) {
    [$service] = bookableService(1);

    $this->getJson(route('booking.slots', ['service' => $service->slug, ...$query]))->assertUnprocessable();
})->with([
    'unknown service' => [['service' => 'unknown', 'date' => '2026-10-20']],
    'no month or date' => [[]],
    'malformed date' => [['date' => '20.10.2026']],
]);

test('a valid booking is stored awaiting its deposit', function () {
    [$service, $stylists] = bookableService(1);

    $response = $this->post(route('booking.store'), bookingPayload($service, ['date' => '2026-10-27']));

    $booking = Booking::sole();
    $response->assertRedirect(route('booking.success'))
        ->assertSessionHas('booking_token', $booking->manage_token);

    expect($booking)
        ->status->toBe(BookingStatus::PendingDeposit)
        ->stylist_id->toBe($stylists[0]->id)
        ->customer_name->toBe('Ана Петровска')
        ->deposit_amount->toBe(500)
        ->and($booking->starts_at->toDateTimeString())->toBe('2026-10-27 10:00:00')
        ->and($booking->ends_at->toDateTimeString())->toBe('2026-10-27 11:00:00')
        ->and($booking->deposit_due_at->toDateTimeString())->toBe('2026-10-21 08:00:00')
        ->and($booking->consent_at)->not->toBeNull()
        ->and($booking->manage_token)->toHaveLength(48);
});

test('the deposit deadline never falls after the appointment', function () {
    [$service] = bookableService(1);
    WorkingHour::factory()->on(1, '09:00', '17:00')->create();

    $this->post(route('booking.store'), bookingPayload($service, ['date' => '2026-10-19', 'time' => '11:00']));

    expect(Booking::sole()->deposit_due_at->toDateTimeString())->toBe('2026-10-19 11:00:00');
});

test('the success page shows the booking, the deposit rule and the manage link', function () {
    [$service] = bookableService(1);
    $this->post(route('booking.store'), bookingPayload($service));
    $booking = Booking::sole();

    $this->get(route('booking.success'))
        ->assertOk()
        ->assertSee($service->name)
        ->assertSee('10:00')
        ->assertSee('Капар од 500 ден., во готово, во салонот')
        ->assertSee(route('booking.show', $booking));
});

test('the success page without a fresh booking sends the visitor to the wizard', function () {
    $this->get(route('booking.success'))->assertRedirect(route('booking.create'));
});

test('booking any stylist assigns one who is free', function () {
    [$service, $stylists] = bookableService(2);
    Booking::factory()->for($stylists[0])->for($service)->at('2026-10-20 10:00')->create();

    $this->post(route('booking.store'), bookingPayload($service))
        ->assertRedirect(route('booking.success'));

    expect(Booking::latest('id')->first()->stylist_id)->toBe($stylists[1]->id);
});

test('a slot that is already taken cannot be booked twice', function () {
    [$service, $stylists] = bookableService(1);

    $this->post(route('booking.store'), bookingPayload($service))->assertRedirect(route('booking.success'));
    $this->post(route('booking.store'), bookingPayload($service, ['customer_email' => 'second@example.com']))
        ->assertRedirect(route('booking.create', ['service' => $service->slug, 'stylist' => 'any', 'date' => '2026-10-20']))
        ->assertSessionHasErrors(['time' => 'За жал, овој термин штотуку беше зафатен. Изберете друго време.']);

    expect(Booking::count())->toBe(1);
});

test('a time outside working hours or off the slot grid is rejected', function (string $time) {
    [$service] = bookableService(1);

    $this->post(route('booking.store'), bookingPayload($service, ['time' => $time]))
        ->assertSessionHasErrors('time');

    expect(Booking::count())->toBe(0);
})->with([
    'before opening' => ['07:00'],
    'ending after closing' => ['16:30'],
    'off the grid' => ['10:10'],
]);

test('a stylist who does not perform the service cannot be booked for it', function () {
    [$service] = bookableService(1);
    $otherStylist = Stylist::factory()->create();

    $this->post(route('booking.store'), bookingPayload($service, ['stylist' => $otherStylist->id]))
        ->assertSessionHasErrors('time');

    expect(Booking::count())->toBe(0);
});

test('an empty booking form reports every required field in Macedonian', function () {
    $this->post(route('booking.store'), [])
        ->assertSessionHasErrors([
            'service' => 'Полето услуга е задолжително.',
            'date' => 'Полето датум е задолжително.',
            'time' => 'Полето време е задолжително.',
            'customer_name' => 'Полето име и презиме е задолжително.',
            'customer_phone' => 'Полето телефон е задолжително.',
            'customer_email' => 'Полето е-пошта е задолжително.',
            'consent' => 'Мора да се согласите со обработката на личните податоци.',
        ]);
});

test('invalid customer details are rejected', function (string $field, string $value) {
    [$service] = bookableService(1);

    $this->post(route('booking.store'), bookingPayload($service, [$field => $value]))
        ->assertSessionHasErrors($field);

    expect(Booking::count())->toBe(0);
})->with([
    'email' => ['customer_email', 'not-an-email'],
    'phone with letters' => ['customer_phone', 'call me'],
    'missing consent' => ['consent', '0'],
]);

test('a filled honeypot field is rejected', function () {
    [$service] = bookableService(1);

    $this->post(route('booking.store'), bookingPayload($service, ['website' => 'http://spam.example']))
        ->assertSessionHasErrors('website');

    expect(Booking::count())->toBe(0);
});

test('the manage page shows the booking for its token', function () {
    $booking = Booking::factory()->at('2026-10-20 10:00')->create();

    $this->get(route('booking.show', $booking))
        ->assertOk()
        ->assertSee($booking->service->name)
        ->assertSee('Чека капар')
        ->assertSee('Откажи резервација');
});

test('an unknown manage token returns not found', function () {
    $this->get('/rezervacija/unknown-token')->assertNotFound();
});

test('a customer can cancel an upcoming booking', function () {
    $booking = Booking::factory()->at('2026-10-20 10:00')->create();

    $this->delete(route('booking.destroy', $booking))
        ->assertRedirect(route('booking.show', $booking))
        ->assertSessionHas('status', 'Резервацијата е откажана.');

    expect($booking->fresh())
        ->status->toBe(BookingStatus::Cancelled)
        ->cancelled_at->not->toBeNull()
        ->cancel_reason->toBe(Booking::CANCELLED_BY_CUSTOMER);
});

test('a past or already cancelled booking cannot be cancelled', function (Booking $booking) {
    $originalStatus = $booking->status;

    $this->from(route('booking.show', $booking))
        ->delete(route('booking.destroy', $booking))
        ->assertRedirect(route('booking.show', $booking))
        ->assertSessionHasErrors('booking');

    expect($booking->fresh()->status)->toBe($originalStatus);
})->with([
    'past' => fn () => Booking::factory()->at('2026-10-18 10:00')->confirmed()->create(),
    'cancelled' => fn () => Booking::factory()->at('2026-10-20 10:00')->cancelled()->create(),
]);
