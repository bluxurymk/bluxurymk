<x-layouts::app :title="__('booking.success.title')">
    <x-page-header :eyebrow="__('booking.success.eyebrow')" :title="__('booking.success.title')">{{ __('booking.success.intro') }}</x-page-header>

    <section class="mx-auto flex max-w-3xl flex-col gap-8 px-4 py-16 sm:px-6 lg:px-8">
        <div class="rounded-sm border border-gold-500/30 bg-marble-900/70 p-6 sm:p-8">
            <x-booking-summary :booking="$booking" />
        </div>

        <x-deposit-notice :booking="$booking" />

        <div class="flex flex-col gap-3 text-sm text-beige/80">
            <p>{{ __('booking.success.manage_hint') }}</p>
            <a href="{{ route('booking.show', $booking) }}" class="break-all text-gold-300 underline underline-offset-4 hover:text-gold-400">{{ route('booking.show', $booking) }}</a>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <x-button :href="route('home')" variant="outline">{{ __('site.nav.home') }}</x-button>
            <x-button variant="outline" :href="'tel:'.str_replace(' ', '', config('salon.phone'))">
                <x-icon name="phone" class="size-4" /> {{ config('salon.phone') }}
            </x-button>
        </div>
    </section>
</x-layouts::app>
