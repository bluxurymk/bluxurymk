@php
    $socials = array_filter(config('salon.socials'));
    $phoneHref = 'tel:'.str_replace(' ', '', config('salon.phone'));
@endphp

<footer class="bg-marble border-t border-gold-500/30">
    <div class="mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-3 lg:px-8">
        <div class="flex flex-col items-center gap-5 text-center lg:items-start lg:text-left">
            <x-logo />
            <p class="font-script text-2xl text-gold-300">{{ __('site.tagline') }}</p>
            <p class="text-xs uppercase tracking-[0.3em] text-beige/60">{{ __('site.tagline_secondary') }}</p>

            <div class="flex flex-col items-center gap-3 lg:items-start">
                <h2 class="text-xs font-semibold uppercase tracking-[0.25em] text-gold-400">{{ __('site.footer.follow') }}</h2>
                @if ($socials)
                    <div class="flex gap-3">
                        @foreach ($socials as $network => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($network) }}" class="rounded-full transition hover:brightness-125">
                                <x-icon-circle :name="$network" size="sm" />
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-beige/60">{{ __('site.footer.socials_soon') }}</p>
                @endif
            </div>
        </div>

        <div class="flex flex-col items-center gap-5 lg:items-start">
            <h2 class="text-xs font-semibold uppercase tracking-[0.25em] text-gold-400">{{ __('site.footer.contact') }}</h2>
            <ul class="flex flex-col gap-4 text-sm">
                <li>
                    <a href="{{ $phoneHref }}" class="flex items-center gap-3 hover:text-gold-300">
                        <x-icon-circle name="phone" size="sm" />
                        <span><span class="sr-only">{{ __('site.labels.phone') }}: </span>{{ config('salon.phone') }}</span>
                    </a>
                </li>
                <li>
                    <a href="mailto:{{ config('salon.email') }}" class="flex items-center gap-3 break-all hover:text-gold-300">
                        <x-icon-circle name="mail" size="sm" />
                        <span><span class="sr-only">{{ __('site.labels.email') }}: </span>{{ config('salon.email') }}</span>
                    </a>
                </li>
                <li class="flex items-center gap-3">
                    <x-icon-circle name="map-pin" size="sm" />
                    <span><span class="sr-only">{{ __('site.labels.address') }}: </span>{{ config('salon.address') }}, {{ config('salon.city') }}</span>
                </li>
            </ul>

            <h2 class="pt-2 text-xs font-semibold uppercase tracking-[0.25em] text-gold-400">{{ __('site.footer.links') }}</h2>
            <ul class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm text-beige/80">
                <li><a href="{{ route('services.index') }}" class="hover:text-gold-300">{{ __('site.nav.services') }}</a></li>
                <li><a href="{{ route('gallery') }}" class="hover:text-gold-300">{{ __('site.nav.gallery') }}</a></li>
                <li><a href="{{ route('about') }}" class="hover:text-gold-300">{{ __('site.nav.about') }}</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-gold-300">{{ __('site.nav.contact') }}</a></li>
                <li class="col-span-2"><a href="{{ route('privacy') }}" class="hover:text-gold-300">{{ __('site.nav.privacy') }}</a></li>
            </ul>
        </div>

        <div class="overflow-hidden rounded-sm border border-gold-500/30">
            <iframe
                src="https://www.google.com/maps?q={{ urlencode(config('salon.map_query')) }}&output=embed"
                title="{{ __('site.footer.map_title') }}"
                class="h-72 w-full grayscale-[60%] sepia-[25%] lg:h-full lg:min-h-72"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
        </div>
    </div>

    <div class="border-t border-gold-500/15">
        <p class="mx-auto max-w-7xl px-4 py-6 text-center text-xs text-beige/50 sm:px-6 lg:px-8">
            &copy; {{ date('Y') }} {{ config('salon.name') }}. {{ __('site.footer.rights') }}
        </p>
    </div>
</footer>
