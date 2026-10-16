<section class="relative overflow-hidden border-y border-gold-500/30 bg-marble">
    <div class="absolute inset-x-0 top-0 h-px bg-linear-to-r from-transparent via-gold-400 to-transparent" aria-hidden="true"></div>
    <div class="mx-auto flex max-w-4xl flex-col items-center gap-6 px-4 py-20 text-center sm:px-6">
        <p class="font-script text-2xl text-gold-300">{{ __('site.labels.we_await') }}</p>
        <h2 class="text-gold-gradient shimmer font-serif text-4xl font-semibold uppercase tracking-wide sm:text-6xl">{{ __('site.cta.band_title') }}</h2>
        <x-gold-divider />
        <p class="max-w-xl text-beige/80">{{ __('site.cta.band_text') }}</p>
        <div class="flex flex-col gap-3 sm:flex-row">
            <x-button :href="route('booking.create')">{{ __('site.cta.book') }}</x-button>
            <x-button variant="outline" :href="'tel:'.str_replace(' ', '', config('salon.phone'))">
                <x-icon name="phone" class="size-4" /> {{ config('salon.phone') }}
            </x-button>
        </div>
    </div>
</section>
