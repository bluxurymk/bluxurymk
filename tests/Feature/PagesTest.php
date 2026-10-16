<?php

test('public pages render with the shared layout', function (string $routeName, string $expectedText) {
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
    'booking placeholder' => ['booking.create', 'Онлајн резервацијата наскоро'],
]);

test('home page lists every service with a link to its detail page', function () {
    $response = $this->get(route('home'))->assertOk();

    foreach (config('salon.services') as $slug => $service) {
        $response->assertSee($service['name'])
            ->assertSee(route('services.show', $slug));
    }
});

test('service detail page shows the service', function () {
    $this->get(route('services.show', 'keratin'))
        ->assertOk()
        ->assertSee('Кератин')
        ->assertSee('За здрава и сјајна коса');
});

test('unknown service returns not found', function () {
    $this->get(route('services.show', 'does-not-exist'))->assertNotFound();
});
