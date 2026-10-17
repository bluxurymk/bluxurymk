<x-layouts::app :title="$service->name" :description="$service->subtitle">
    <x-page-header :eyebrow="$service->subtitle" :title="$service->name" />

    <section class="mx-auto grid max-w-6xl items-center gap-12 px-4 py-20 sm:px-6 lg:grid-cols-5 lg:px-8">
        <x-photo-frame :image="'service-'.$service->slug" shape="oval" :alt="$service->name" :placeholder="'Фото: '.$service->name" class="mx-auto w-full max-w-xs lg:col-span-2" />

        <div class="flex flex-col items-center gap-6 text-center lg:col-span-3 lg:items-start lg:text-left">
            <x-icon-circle :name="$service->icon" size="lg" />
            <p class="text-lg leading-relaxed text-beige/85">{{ $service->description }}</p>
            <p class="inline-flex items-center gap-2 text-sm uppercase tracking-[0.2em] text-gold-300">
                <x-icon name="clock" class="size-5" />
                {{ __('site.labels.duration') }}: ~{{ $service->duration_min }} {{ __('site.labels.minutes') }}
            </p>
            <div class="flex flex-col gap-3 sm:flex-row">
                <x-button :href="route('booking.create', ['service' => $service->slug])">{{ __('site.cta.book') }}</x-button>
                <x-button :href="route('services.index')" variant="outline">{{ __('site.cta.all_services') }}</x-button>
            </div>
        </div>
    </section>

    <section class="border-t border-gold-500/15">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <x-section-title title="Други услуги" />
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($otherServices as $other)
                    <x-service-card :service="$other" />
                @endforeach
            </div>
        </div>
    </section>
</x-layouts::app>
