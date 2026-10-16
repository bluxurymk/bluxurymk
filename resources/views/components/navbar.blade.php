@php
    $links = [
        'home' => route('home'),
        'services' => route('services.index'),
        'gallery' => route('gallery'),
        'about' => route('about'),
        'contact' => route('contact'),
    ];

    $isActive = fn (string $key): bool => match ($key) {
        'home' => request()->routeIs('home'),
        'services' => request()->routeIs('services.*'),
        default => request()->routeIs($key),
    };
@endphp

<header x-data="{ open: false }" @keydown.escape.window="open = false" class="sticky top-0 z-50 border-b border-gold-500/20 bg-marble-950/90 backdrop-blur-md">
    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="shrink-0" aria-label="{{ config('salon.name') }} — {{ __('site.nav.home') }}">
            <x-logo />
        </a>

        <nav class="hidden lg:block" aria-label="{{ __('site.nav.main') }}">
            <ul class="flex items-center gap-8">
                @foreach ($links as $key => $url)
                    <li>
                        <a href="{{ $url }}" @if ($isActive($key)) aria-current="page" @endif @class([
                            'relative py-2 text-xs font-medium uppercase tracking-[0.2em] transition hover:text-gold-300',
                            'text-gold-400 after:absolute after:inset-x-0 after:-bottom-0.5 after:h-px after:bg-gold-500' => $isActive($key),
                            'text-beige/80' => ! $isActive($key),
                        ])>{{ __('site.nav.'.$key) }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="flex items-center gap-2">
            <x-button :href="route('booking.create')" class="px-4! py-2.5! text-[0.65rem]! sm:px-6! sm:text-xs!">
                {{ __('site.cta.book') }}
            </x-button>
            <button type="button" @click="open = ! open" :aria-expanded="open" aria-controls="mobile-menu" class="inline-flex size-11 items-center justify-center rounded-full text-gold-300 hover:bg-gold-500/10 lg:hidden">
                <span class="sr-only" x-text="open ? @js(__('site.nav.menu_close')) : @js(__('site.nav.menu_open'))">{{ __('site.nav.menu_open') }}</span>
                <x-icon name="menu" x-show="! open" />
                <x-icon name="close" x-show="open" x-cloak />
            </button>
        </div>
    </div>

    <nav id="mobile-menu" x-show="open" x-cloak x-transition.opacity.duration.200ms class="border-t border-gold-500/20 bg-marble-950 lg:hidden" aria-label="{{ __('site.nav.main') }}">
        <ul class="flex flex-col px-4 py-4 sm:px-6">
            @foreach ($links as $key => $url)
                <li>
                    <a href="{{ $url }}" @if ($isActive($key)) aria-current="page" @endif @class([
                        'block border-b border-gold-500/10 py-4 font-serif text-xl uppercase tracking-widest',
                        'text-gold-400' => $isActive($key),
                        'text-beige/90' => ! $isActive($key),
                    ])>{{ __('site.nav.'.$key) }}</a>
                </li>
            @endforeach
            <li class="pt-5">
                <a href="tel:{{ str_replace(' ', '', config('salon.phone')) }}" class="inline-flex items-center gap-3 text-gold-300">
                    <x-icon name="phone" class="size-5" /> {{ config('salon.phone') }}
                </a>
            </li>
        </ul>
    </nav>
</header>
