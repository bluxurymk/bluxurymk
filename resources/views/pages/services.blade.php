<x-layouts::app :title="__('site.nav.services')">
    <x-page-header eyebrow="Што нудиме" :title="__('site.nav.services')">
        Од класично шишање до напредни техники на боење — изберете ја услугата која ви одговара.
    </x-page-header>

    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                <x-service-card :service="$service" @class(['lg:col-start-2' => $loop->last && $loop->count % 3 === 1]) />
            @endforeach
        </div>
    </section>

    <x-cta-band />
</x-layouts::app>
