<?php

use App\Models\BlockedSlot;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Stylist;
use App\Models\WorkingHour;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    config([
        'salon.booking.buffer_minutes' => 15,
        'salon.booking.slot_interval_minutes' => 30,
        'salon.booking.min_notice_minutes' => 120,
        'salon.booking.max_days_ahead' => 60,
    ]);

    // Monday 2026-10-19, 08:00. Tuesday is 2026-10-20.
    $this->travelTo(CarbonImmutable::parse('2026-10-19 08:00'));
});

/**
 * @return array{0: Service, 1: Stylist}
 */
function serviceWithStylist(int $durationMinutes = 60): array
{
    $service = Service::factory()->lasting($durationMinutes)->create();
    $stylist = Stylist::factory()->create();
    $stylist->services()->attach($service);

    return [$service, $stylist];
}

function slotTimes(AvailabilityService $availability, Service $service, string $date, ?Stylist $stylist = null): array
{
    return $availability->availableSlots($service, CarbonImmutable::parse($date), $stylist)->keys()->all();
}

test('slots fit the service duration inside salon hours on the slot grid', function () {
    [$service] = serviceWithStylist(60);
    WorkingHour::factory()->on(2, '09:00', '12:00')->create();

    expect(slotTimes(app(AvailabilityService::class), $service, '2026-10-20'))
        ->toBe(['09:00', '09:30', '10:00', '10:30', '11:00']);
});

test('a day without working hours has no slots', function () {
    [$service] = serviceWithStylist();
    WorkingHour::factory()->on(2)->create();

    expect(slotTimes(app(AvailabilityService::class), $service, '2026-10-25'))->toBe([]);
});

test('an existing booking blocks overlapping slots including the buffer', function () {
    [$service, $stylist] = serviceWithStylist(60);
    WorkingHour::factory()->on(2, '09:00', '14:00')->create();
    Booking::factory()->for($stylist)->for($service)->at('2026-10-20 10:00', 60)->create();

    expect(slotTimes(app(AvailabilityService::class), $service, '2026-10-20'))
        ->toBe(['11:30', '12:00', '12:30', '13:00']);
});

test('a cancelled booking frees its slot', function () {
    [$service, $stylist] = serviceWithStylist(60);
    WorkingHour::factory()->on(2, '09:00', '11:00')->create();
    Booking::factory()->for($stylist)->for($service)->at('2026-10-20 09:00', 60)->cancelled()->create();

    expect(slotTimes(app(AvailabilityService::class), $service, '2026-10-20'))
        ->toBe(['09:00', '09:30', '10:00']);
});

test('a salon-wide blocked period removes slots for everyone', function () {
    [$service] = serviceWithStylist(60);
    WorkingHour::factory()->on(2, '09:00', '12:00')->create();
    BlockedSlot::factory()->between('2026-10-20 09:00', '2026-10-20 10:30')->create();

    expect(slotTimes(app(AvailabilityService::class), $service, '2026-10-20'))
        ->toBe(['10:30', '11:00']);
});

test('a blocked period for one stylist leaves the other stylists bookable', function () {
    [$service, $blockedStylist] = serviceWithStylist(60);
    $freeStylist = Stylist::factory()->create();
    $freeStylist->services()->attach($service);
    WorkingHour::factory()->on(2, '09:00', '10:00')->create();
    BlockedSlot::factory()->for($blockedStylist)->between('2026-10-20 00:00', '2026-10-20 23:59')->create();

    $availability = app(AvailabilityService::class);

    expect(slotTimes($availability, $service, '2026-10-20', $blockedStylist))->toBe([])
        ->and(slotTimes($availability, $service, '2026-10-20', $freeStylist))->toBe(['09:00']);
});

test('slots inside the minimum notice period are not offered', function () {
    [$service] = serviceWithStylist(60);
    WorkingHour::factory()->on(1, '09:00', '12:00')->create();

    expect(slotTimes(app(AvailabilityService::class), $service, '2026-10-19'))
        ->toBe(['10:00', '10:30', '11:00']);
});

test('dates past the maximum days ahead are not bookable', function () {
    [$service] = serviceWithStylist(60);
    WorkingHour::factory()->on(2)->create();
    $availability = app(AvailabilityService::class);

    // 2026-12-15 is a Tuesday 57 days ahead, 2026-12-22 is 64 days ahead.
    expect(slotTimes($availability, $service, '2026-12-15'))->not->toBe([])
        ->and(slotTimes($availability, $service, '2026-12-22'))->toBe([]);
});

test('with any stylist a time is free when at least one stylist is free', function () {
    [$service, $busyStylist] = serviceWithStylist(60);
    $freeStylist = Stylist::factory()->create();
    $freeStylist->services()->attach($service);
    WorkingHour::factory()->on(2, '09:00', '10:00')->create();
    Booking::factory()->for($busyStylist)->for($service)->at('2026-10-20 09:00', 60)->create();

    $availability = app(AvailabilityService::class);
    $nineOClock = CarbonImmutable::parse('2026-10-20 09:00');

    expect(slotTimes($availability, $service, '2026-10-20'))->toBe(['09:00'])
        ->and(slotTimes($availability, $service, '2026-10-20', $busyStylist))->toBe([])
        ->and($availability->freeStylistAt($service, $nineOClock)?->is($freeStylist))->toBeTrue()
        ->and($availability->freeStylistAt($service, $nineOClock, $busyStylist))->toBeNull();
});

test('stylist-specific hours replace the salon hours', function () {
    [$service, $stylist] = serviceWithStylist(60);
    WorkingHour::factory()->on(2, '09:00', '17:00')->create();
    WorkingHour::factory()->for($stylist)->on(2, '14:00', '16:00')->create();

    expect(slotTimes(app(AvailabilityService::class), $service, '2026-10-20', $stylist))
        ->toBe(['14:00', '14:30', '15:00']);
});

test('stylists who are inactive or do not perform the service are ignored', function () {
    $service = Service::factory()->create();
    Stylist::factory()->inactive()->create()->services()->attach($service);
    Stylist::factory()->create();
    WorkingHour::factory()->on(2)->create();

    expect(slotTimes(app(AvailabilityService::class), $service, '2026-10-20'))->toBe([]);
});

test('available dates lists only days with a free slot', function () {
    [$service] = serviceWithStylist(60);
    WorkingHour::factory()->on(2)->create();
    WorkingHour::factory()->on(4)->create();
    BlockedSlot::factory()->between('2026-10-22 00:00', '2026-10-22 23:59')->create();

    $dates = app(AvailabilityService::class)
        ->availableDates($service, null, CarbonImmutable::parse('2026-10-19'), CarbonImmutable::parse('2026-10-31'))
        ->map->toDateString()
        ->all();

    expect($dates)->toBe(['2026-10-20', '2026-10-27', '2026-10-29']);
});
