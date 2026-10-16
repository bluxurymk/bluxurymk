<?php

use App\Filament\Pages\BookingCalendar;
use App\Models\Booking;
use App\Models\User;
use Carbon\CarbonImmutable;
use Livewire\Livewire;

test('guests are sent to the admin login', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

test('every admin page loads for staff', function (string $path) {
    $this->actingAs(User::factory()->create())
        ->get($path)
        ->assertOk();
})->with([
    'dashboard' => ['/admin'],
    'calendar' => ['/admin/kalendar'],
    'bookings' => ['/admin/bookings'],
    'new booking' => ['/admin/bookings/create'],
    'services' => ['/admin/services'],
    'stylists' => ['/admin/stylists'],
    'working hours' => ['/admin/working-hours'],
    'blocked slots' => ['/admin/blocked-slots'],
    'gallery' => ['/admin/gallery-images'],
    'settings' => ['/admin/podesuvanja'],
]);

test('the calendar lists the week bookings and skips cancelled ones', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-19 08:00'));
    $this->actingAs(User::factory()->create());

    $booking = Booking::factory()->at('2026-10-21 10:00')->create();
    $cancelled = Booking::factory()->at('2026-10-21 12:00')->cancelled()->create();
    $nextWeek = Booking::factory()->at('2026-10-28 10:00')->create();

    Livewire::test(BookingCalendar::class)
        ->assertSee($booking->customer_name)
        ->assertDontSee($cancelled->customer_name)
        ->assertDontSee($nextWeek->customer_name)
        ->call('nextWeek')
        ->assertSee($nextWeek->customer_name);
});
