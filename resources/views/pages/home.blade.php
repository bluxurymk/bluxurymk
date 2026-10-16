<x-layouts::app>
    {{-- Hero --}}
    <section class="bg-marble relative overflow-hidden">
        <div class="absolute -top-40 -right-40 size-[32rem] rounded-full bg-gold-500/10 blur-3xl" aria-hidden="true"></div>
        <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 sm:py-24 lg:grid-cols-2 lg:px-8">
            <div class="flex flex-col items-center gap-7 text-center lg:items-start lg:text-left">
                <h1 class="sr-only">{{ config('salon.name') }}, {{ config('salon.city') }}</h1>
                <x-logo size="lg" />
                <x-gold-divider class="lg:justify-start" />
                <p class="font-script text-3xl text-gold-300 sm:text-4xl">{{ __('site.tagline') }}</p>
                <p class="text-xs font-medium uppercase tracking-[0.35em] text-beige/70">{{ __('site.tagline_secondary') }}</p>
                <div class="flex flex-col items-center gap-4 sm:flex-row">
                    <x-button :href="route('booking.create')">{{ __('site.cta.book') }}</x-button>
                    <a href="tel:{{ str_replace(' ', '', config('salon.phone')) }}" class="inline-flex items-center gap-3 font-serif text-2xl text-gold-300 hover:text-gold-400">
                        <x-icon-circle name="phone" size="sm" />
                        {{ config('salon.phone') }}
                    </a>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-md">
                <x-photo-frame shape="oval" alt="Ентериер на салонот" class="w-4/5" />
                <x-photo-frame alt="Работа на салонот" class="absolute -right-2 bottom-8 w-2/5 sm:right-0" />
                <x-photo-frame alt="Алатки за фризура" class="absolute right-8 -bottom-6 w-1/4 sm:right-24" />
            </div>
        </div>
    </section>

    {{-- Services --}}
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <x-section-title eyebrow="Што нудиме" :title="__('site.nav.services')">
            Секоја услуга е изработена со врвни производи и многу љубов кон деталите.
        </x-section-title>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                <x-service-card :service="$service" @class(['lg:col-start-2' => $loop->last && $loop->count % 3 === 1]) />
            @endforeach
        </div>
    </section>

    {{-- About teaser --}}
    <section class="bg-marble-light text-marble-900">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:px-8">
            <x-photo-frame alt="Нашиот тим" class="mx-auto w-full max-w-sm" />
            <div class="flex flex-col items-center gap-6 text-center lg:items-start lg:text-left">
                <x-section-title eyebrow="Добредојдовте" title="За нас" :dark="false" class="lg:items-start lg:text-left" />
                <p class="leading-relaxed text-marble-700">
                    B Luxury Hair Salon е простор каде стилот, квалитетот и убавината се среќаваат. Во срцето на Куманово создадовме луксузна и топла атмосфера во која секоја клиентка добива внимание, совет и резултат со кој ќе се чувствува прекрасно.
                </p>
                <x-button :href="route('about')" variant="outline" class="border-gold-600! text-gold-600! hover:bg-gold-600/10!">
                    {{ __('site.cta.read_more') }}
                </x-button>
            </div>
        </div>
    </section>

    <x-cta-band />
</x-layouts::app>
