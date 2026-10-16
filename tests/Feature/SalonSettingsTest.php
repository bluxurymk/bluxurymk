<?php

use App\Filament\Pages\Settings;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Livewire\Livewire;

test('the settings form starts from the config defaults', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Settings::class)
        ->assertSchemaStateSet([
            'phone' => config('salon.phone'),
            'booking.deposit_amount' => config('salon.booking.deposit_amount'),
        ]);
});

test('saved settings replace the defaults on the public site', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Settings::class)
        ->fillForm([
            'phone' => '+389 70 111 222',
            'socials.instagram' => 'https://instagram.com/bluxury',
            'booking.deposit_amount' => 1000,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    expect(Setting::firstWhere('key', 'booking.deposit_amount')->value)->toBe(1000);

    $service = Service::factory()->create();

    $this->get(route('home'))
        ->assertSee('+389 70 111 222')
        ->assertDontSee('+389 78 906 112')
        ->assertSee('https://instagram.com/bluxury');

    $this->get(route('booking.create', ['service' => $service->slug]))
        ->assertOk();

    expect(config('salon.booking.deposit_amount'))->toBe(1000);
});

test('settings are validated', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Settings::class)
        ->fillForm(['phone' => '', 'email' => 'not-an-email', 'booking.deposit_hours' => 0])
        ->call('save')
        ->assertHasFormErrors(['phone' => 'required', 'email' => 'email', 'booking.deposit_hours' => 'min']);

    expect(Setting::count())->toBe(0);
});
