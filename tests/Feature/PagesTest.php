<?php

use App\Models\Service;
use Database\Seeders\ServiceSeeder;

test('public pages render with the shared layout', function (string $routeName, string $expectedText) {
    $this->seed(ServiceSeeder::class);

    $this->get(route($routeName))
        ->assertOk()
        ->assertSee($expectedText)
        ->assertSee('Резервирај термин')
        ->assertSee('+389 78 906 112');
})->with([
    'home' => ['home', 'Повеќе од фризура'],
    'services' => ['services.index', 'Балајаж'],
    'gallery' => ['gallery', 'Галерија'],
    'about' => ['about', 'Нашиот тим'],
    'contact' => ['contact', 'boban_hair@yahoo.com'],
    'privacy' => ['privacy', 'Вашите права'],
    'booking' => ['booking.create', 'Изберете услуга'],
]);

test('home page lists every active service with a link to its detail page', function () {
    $services = Service::factory()->count(2)->create();
    $inactive = Service::factory()->inactive()->create();

    $response = $this->get(route('home'))->assertOk();

    foreach ($services as $service) {
        $response->assertSee($service->name)
            ->assertSee(route('services.show', $service));
    }

    $response->assertDontSee($inactive->name);
});

test('service detail page shows the service and links to booking it', function () {
    $service = Service::factory()->create();

    $this->get(route('services.show', $service))
        ->assertOk()
        ->assertSee($service->name)
        ->assertSee($service->subtitle)
        ->assertSee(route('booking.create', ['service' => $service->slug]), escape: false);
});

test('unknown service returns not found', function () {
    $this->get('/uslugi/does-not-exist')->assertNotFound();
});

test('inactive service returns not found', function () {
    $service = Service::factory()->inactive()->create();

    $this->get(route('services.show', $service))->assertNotFound();
});
