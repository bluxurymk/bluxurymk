<?php

use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\Pages\CreateBooking;
use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Stylist;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(function () {
    config(['salon.booking.deposit_amount' => 500, 'salon.booking.deposit_hours' => 48]);

    $this->travelTo(CarbonImmutable::parse('2026-10-19 08:00'));
    $this->actingAs(User::factory()->create());
});

test('staff can mark the deposit as received', function () {
    $booking = Booking::factory()->at('2026-10-22 10:00')->create();

    Livewire::test(ListBookings::class)
        ->callAction(TestAction::make('confirmDeposit')->table($booking))
        ->assertNotified();

    expect($booking->fresh())
        ->status->toBe(BookingStatus::Confirmed)
        ->deposit_paid_at->not->toBeNull();
});

test('staff can cancel a booking with a reason', function () {
    $booking = Booking::factory()->at('2026-10-22 10:00')->confirmed()->create();

    Livewire::test(ListBookings::class)
        ->callAction(TestAction::make('cancel')->table($booking), data: ['reason' => 'Клиентот се јави'])
        ->assertNotified();

    expect($booking->fresh())
        ->status->toBe(BookingStatus::Cancelled)
        ->cancel_reason->toBe('Клиентот се јави');
});

test('completed and no-show are only offered once the appointment has started', function () {
    $upcoming = Booking::factory()->at('2026-10-22 10:00')->confirmed()->create();
    $past = Booking::factory()->at('2026-10-18 10:00')->confirmed()->create();

    Livewire::test(ListBookings::class)
        ->filterTable('upcoming', false)
        ->assertActionHidden(TestAction::make('markNoShow')->table($upcoming))
        ->assertActionHidden(TestAction::make('markCompleted')->table($upcoming))
        ->assertActionVisible(TestAction::make('markCompleted')->table($past))
        ->callAction(TestAction::make('markNoShow')->table($past));

    expect($past->fresh()->status)->toBe(BookingStatus::NoShow);
});

test('the deposit action is hidden once the deposit is paid', function () {
    $booking = Booking::factory()->at('2026-10-22 10:00')->confirmed()->create();

    Livewire::test(ListBookings::class)
        ->assertActionHidden(TestAction::make('confirmDeposit')->table($booking));
});

test('staff can enter a phone booking without an email', function () {
    $service = Service::factory()->lasting(90)->create();
    $stylist = Stylist::factory()->hasAttached($service)->create();

    Livewire::test(CreateBooking::class)
        ->fillForm([
            'service_id' => $service->id,
            'stylist_id' => $stylist->id,
            'starts_at' => '2026-10-22 10:00',
            'status' => BookingStatus::Confirmed->value,
            'customer_name' => 'Марија Стојанова',
            'customer_phone' => '071 222 333',
            'customer_email' => null,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Booking::sole())
        ->status->toBe(BookingStatus::Confirmed)
        ->customer_email->toBeNull()
        ->deposit_paid_at->not->toBeNull()
        ->manage_token->toHaveLength(48)
        ->and(Booking::sole()->ends_at->toDateTimeString())->toBe('2026-10-22 11:30:00');
});

test('staff cannot double-book a stylist', function () {
    $service = Service::factory()->lasting(60)->create();
    $stylist = Stylist::factory()->hasAttached($service)->create();
    Booking::factory()->for($service)->for($stylist)->at('2026-10-22 10:00')->create();

    Livewire::test(CreateBooking::class)
        ->fillForm([
            'service_id' => $service->id,
            'stylist_id' => $stylist->id,
            'starts_at' => '2026-10-22 10:30',
            'status' => BookingStatus::PendingDeposit->value,
            'customer_name' => 'Марија Стојанова',
            'customer_phone' => '071 222 333',
        ])
        ->call('create')
        ->assertHasFormErrors(['starts_at']);

    expect(Booking::count())->toBe(1);
});

test('rescheduling a booking moves its end time too', function () {
    $service = Service::factory()->lasting(60)->create();
    $stylist = Stylist::factory()->hasAttached($service)->create();
    $booking = Booking::factory()->for($service)->for($stylist)->at('2026-10-22 10:00')->create();

    Livewire::test(EditBooking::class, ['record' => $booking->id])
        ->fillForm(['starts_at' => '2026-10-23 15:00'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($booking->fresh())
        ->starts_at->toDateTimeString()->toBe('2026-10-23 15:00:00')
        ->ends_at->toDateTimeString()->toBe('2026-10-23 16:00:00');
});
