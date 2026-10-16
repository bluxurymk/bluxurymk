@php
    $contactItems = [
        ['icon' => 'phone', 'label' => __('site.labels.phone'), 'value' => config('salon.phone'), 'href' => 'tel:'.str_replace(' ', '', config('salon.phone'))],
        ['icon' => 'mail', 'label' => __('site.labels.email'), 'value' => config('salon.email'), 'href' => 'mailto:'.config('salon.email')],
        ['icon' => 'map-pin', 'label' => __('site.labels.address'), 'value' => config('salon.address').', '.config('salon.city'), 'href' => 'https://www.google.com/maps/search/?api=1&query='.urlencode(config('salon.map_query'))],
    ];
@endphp

<x-layouts::app :title="__('site.nav.contact')">
    <x-page-header eyebrow="{{ __('site.labels.we_await') }}" :title="__('site.nav.contact')">
        Јавете ни се, пишете ни или поминете во салонот.
    </x-page-header>

    <section class="mx-auto grid max-w-6xl gap-6 px-4 py-20 sm:px-6 md:grid-cols-3 lg:px-8">
        @foreach ($contactItems as $item)
            <a href="{{ $item['href'] }}" @if ($item['icon'] === 'map-pin') target="_blank" rel="noopener" @endif class="group flex flex-col items-center gap-4 rounded-sm border border-gold-500/20 bg-marble-900/70 p-8 text-center transition hover:border-gold-500/60">
                <x-icon-circle :name="$item['icon']" />
                <span class="text-xs font-semibold uppercase tracking-[0.25em] text-gold-400">{{ $item['label'] }}</span>
                <span class="break-all font-serif text-xl text-beige group-hover:text-gold-300">{{ $item['value'] }}</span>
            </a>
        @endforeach
    </section>

    <section class="mx-auto max-w-6xl px-4 pb-20 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-sm border border-gold-500/30">
            <iframe
                src="https://www.google.com/maps?q={{ urlencode(config('salon.map_query')) }}&output=embed"
                title="{{ __('site.footer.map_title') }}"
                class="h-96 w-full grayscale-[60%] sepia-[25%]"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
        </div>
    </section>

    <x-cta-band />
</x-layouts::app>
