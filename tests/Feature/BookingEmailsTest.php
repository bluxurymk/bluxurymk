<?php

use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\Pages\CreateBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Mail\AdminNewBooking;
use App\Mail\BookingCancelled;
use App\Mail\BookingConfirmed;
use App\Mail\BookingReceived;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Stylist;
use App\Models\User;
use App\Models\WorkingHour;
use Carbon\CarbonImmutable;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    config([
        'salon.email' => 'salon@example.com',
        'salon.booking.deposit_amount' => 500,
        'salon.booking.deposit_hours' => 48,
        'salon.booking.min_notice_minutes' => 120,
    ]);

    // Monday 2026-10-19, 08:00.
    $this->travelTo(CarbonImmutable::parse('2026-10-19 08:00'));
});

test('an online booking emails the customer the deposit rules and notifies the salon', function () {
    Mail::fake();
    $service = Service::factory()->lasting(60)->create();
    Stylist::factory()->hasAttached($service)->create();
    WorkingHour::factory()->on(4, '09:00', '17:00')->create();

    $this->post(route('booking.store'), [
        'service' => $service->slug,
        'date' => '2026-10-22',
        'time' => '10:00',
        'customer_name' => 'Ана Петровска',
        'customer_phone' => '070 123 456',
        'customer_email' => 'ana@example.com',
        'consent' => '1',
    ])->assertRedirect(route('booking.success'));

    $booking = Booking::sole();

    Mail::assertQueued(BookingReceived::class, fn (BookingReceived $mail): bool => $mail->hasTo('ana@example.com') && $mail->booking->is($booking));
    Mail::assertQueued(AdminNewBooking::class, fn (AdminNewBooking $mail): bool => $mail->hasTo('salon@example.com'));
    Mail::assertNotQueued(BookingConfirmed::class);

    expect($booking->received_sent_at)->not->toBeNull();
});

test('a staff booking emails the customer but not the salon', function (string $status, string $expectedMail) {
    Mail::fake();
    $this->actingAs(User::factory()->create());
    $service = Service::factory()->create();
    $stylist = Stylist::factory()->hasAttached($service)->create();

    Livewire::test(CreateBooking::class)
        ->fillForm([
            'service_id' => $service->id,
            'stylist_id' => $stylist->id,
            'starts_at' => '2026-10-22 10:00',
            'status' => $status,
            'customer_name' => 'Марија Стојанова',
            'customer_phone' => '071 222 333',
            'customer_email' => 'marija@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    Mail::assertQueued($expectedMail, fn ($mail): bool => $mail->hasTo('marija@example.com'));
    Mail::assertNotQueued(AdminNewBooking::class);
})->with([
    'awaiting deposit' => [BookingStatus::PendingDeposit->value, BookingReceived::class],
    'already paid' => [BookingStatus::Confirmed->value, BookingConfirmed::class],
]);

test('a staff booking without an email sends nothing', function () {
    Mail::fake();
    $this->actingAs(User::factory()->create());
    $service = Service::factory()->create();
    $stylist = Stylist::factory()->hasAttached($service)->create();

    Livewire::test(CreateBooking::class)
        ->fillForm([
            'service_id' => $service->id,
            'stylist_id' => $stylist->id,
            'starts_at' => '2026-10-22 10:00',
            'status' => BookingStatus::PendingDeposit->value,
            'customer_name' => 'Марија Стојанова',
            'customer_phone' => '071 222 333',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    Mail::assertNothingQueued();
});

test('confirming the deposit emails the customer', function () {
    Mail::fake();
    $this->actingAs(User::factory()->create());
    $booking = Booking::factory()->at('2026-10-22 10:00')->create();

    Livewire::test(ListBookings::class)
        ->callAction(TestAction::make('confirmDeposit')->table($booking));

    Mail::assertQueued(BookingConfirmed::class, fn (BookingConfirmed $mail): bool => $mail->hasTo($booking->customer_email));
});

test('cancelling emails the customer', function () {
    Mail::fake();
    $booking = Booking::factory()->at('2026-10-22 10:00')->create();

    $this->delete(route('booking.destroy', $booking));

    Mail::assertQueued(BookingCancelled::class, fn (BookingCancelled $mail): bool => $mail->hasTo($booking->customer_email));
});

test('a booking that is no longer active is not cancelled or emailed again', function () {
    Mail::fake();
    $booking = Booking::factory()->at('2026-10-22 10:00')->cancelled()->create();

    expect($booking->cancel(Booking::CANCELLED_BY_SALON))->toBeFalse();

    Mail::assertNothingQueued();
});

test('the received email explains the cash deposit and links to the booking', function () {
    $booking = Booking::factory()->at('2026-10-22 10:00')->create([
        'deposit_amount' => 1500,
        'deposit_due_at' => '2026-10-21 08:00',
    ]);

    (new BookingReceived($booking))
        ->assertHasSubject('Резервацијата е примена — чека капар')
        ->assertSeeInHtml('1.500 ден.')
        ->assertSeeInHtml('во готово, во салонот')
        ->assertSeeInHtml('среда, 21 октомври 2026, 08:00')
        ->assertSeeInHtml($booking->service->name)
        ->assertSeeInHtml(route('booking.show', $booking))
        ->assertHasReplyTo('salon@example.com');
});

test('the cancellation email explains why', function (string $reason, string $expectedText) {
    $booking = Booking::factory()->at('2026-10-22 10:00')->cancelled()->create(['cancel_reason' => $reason]);

    (new BookingCancelled($booking))->assertSeeInHtml($expectedText);
})->with([
    'by customer' => [Booking::CANCELLED_BY_CUSTOMER, 'како што побаравте'],
    'deposit expired' => [Booking::CANCELLED_DEPOSIT_EXPIRED, 'капарот не беше платен навреме'],
    'by salon' => [Booking::CANCELLED_BY_SALON, 'салонот мораше да ја откаже'],
    'by salon with reason' => ['Болест', 'Причина: Болест'],
]);

test('the salon notification lists the customer contact details', function () {
    $booking = Booking::factory()->at('2026-10-22 10:00')->create(['customer_phone' => '070 999 888', 'note' => 'Прв пат']);

    (new AdminNewBooking($booking))
        ->assertHasSubject("Нова резервација: {$booking->customer_name}, 22.10. 10:00")
        ->assertSeeInHtml('070 999 888')
        ->assertSeeInHtml('Прв пат')
        ->assertHasReplyTo($booking->customer_email);
});
