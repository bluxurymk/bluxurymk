<?php

use App\Enums\BookingStatus;
use App\Mail\BookingCancelled;
use App\Mail\BookingReminder;
use App\Mail\DepositReminder;
use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    config(['salon.booking.reminder_hours' => 24]);

    // Monday 2026-10-19, 12:00.
    $this->travelTo(CarbonImmutable::parse('2026-10-19 12:00'));
});

test('unpaid bookings past their deadline are cancelled and the customer is told', function () {
    Mail::fake();
    $overdue = Booking::factory()->at('2026-10-25 10:00')->create(['deposit_due_at' => '2026-10-19 11:59']);
    $notYetDue = Booking::factory()->at('2026-10-25 12:00')->create(['deposit_due_at' => '2026-10-19 12:30']);
    $paid = Booking::factory()->at('2026-10-25 14:00')->confirmed()->create(['deposit_due_at' => '2026-10-19 09:00']);

    $this->artisan('bookings:expire-unpaid')->assertSuccessful();

    expect($overdue->fresh())
        ->status->toBe(BookingStatus::Cancelled)
        ->cancel_reason->toBe(Booking::CANCELLED_DEPOSIT_EXPIRED)
        ->and($notYetDue->fresh()->status)->toBe(BookingStatus::PendingDeposit)
        ->and($paid->fresh()->status)->toBe(BookingStatus::Confirmed);

    Mail::assertQueued(BookingCancelled::class, 1);
    Mail::assertQueued(BookingCancelled::class, fn (BookingCancelled $mail): bool => $mail->booking->is($overdue));
});

test('a deposit reminder goes out halfway through the deposit window, once', function () {
    Mail::fake();
    $this->travelTo(CarbonImmutable::parse('2026-10-18 12:00'));
    $booking = Booking::factory()->at('2026-10-25 10:00')->create(['deposit_due_at' => '2026-10-20 12:00']);

    $this->travelTo(CarbonImmutable::parse('2026-10-19 11:00'));
    $this->artisan('bookings:send-reminders')->assertSuccessful();
    Mail::assertNotQueued(DepositReminder::class);

    $this->travelTo(CarbonImmutable::parse('2026-10-19 12:00'));
    $this->artisan('bookings:send-reminders')->assertSuccessful();
    $this->artisan('bookings:send-reminders')->assertSuccessful();

    Mail::assertQueued(DepositReminder::class, 1);
    expect($booking->fresh()->deposit_reminder_sent_at)->not->toBeNull();
});

test('confirmed customers are reminded of tomorrow appointments, once', function () {
    Mail::fake();
    $tomorrow = Booking::factory()->at('2026-10-20 10:00')->confirmed()->create();
    Booking::factory()->at('2026-10-22 10:00')->confirmed()->create();
    Booking::factory()->at('2026-10-20 11:00')->create(['deposit_due_at' => '2026-10-20 11:00']);
    Booking::factory()->at('2026-10-20 12:00')->confirmed()->create(['customer_email' => null]);

    $this->artisan('bookings:send-reminders')->assertSuccessful();
    $this->artisan('bookings:send-reminders')->assertSuccessful();

    Mail::assertQueued(BookingReminder::class, 1);
    Mail::assertQueued(BookingReminder::class, fn (BookingReminder $mail): bool => $mail->booking->is($tomorrow));
    expect($tomorrow->fresh()->reminder_sent_at)->not->toBeNull();
});

test('a deposit confirmed before the expiry run is not cancelled', function () {
    Mail::fake();
    $booking = Booking::factory()->at('2026-10-25 10:00')->create(['deposit_due_at' => '2026-10-19 11:00']);
    $staleCopy = Booking::find($booking->id);

    $booking->confirmDeposit();

    expect($staleCopy->expireUnpaidDeposit())->toBeFalse()
        ->and($booking->fresh()->status)->toBe(BookingStatus::Confirmed);
    Mail::assertNotQueued(BookingCancelled::class);
});
