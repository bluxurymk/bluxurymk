@props(['name'])

<svg {{ $attributes->merge(['class' => 'size-6']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('scissors')
            <circle cx="6" cy="6" r="2.5" /><circle cx="6" cy="18" r="2.5" /><path d="M8 7.5 20 17M8 16.5 20 7M12.5 12h.01" />
            @break
        @case('droplet')
            <path d="M12 3s6 6.4 6 11a6 6 0 0 1-12 0c0-4.6 6-11 6-11Z" /><path d="M9.5 15a2.5 2.5 0 0 0 2.5 2.5" />
            @break
        @case('bowl')
            <path d="M3.5 11h17a8.5 8.5 0 0 1-17 0Z" /><path d="M9 19.5h6M14 11l5-7.5M16.5 4.5l1.8 1.2" />
            @break
        @case('waves')
            <path d="M7 3c-2 4 2 6 0 10s2 6 0 8M12 3c-2 4 2 6 0 10s2 6 0 8M17 3c-2 4 2 6 0 10s2 6 0 8" />
            @break
        @case('sparkle')
            <path d="M12 3c.6 4.4 2.6 6.4 7 7-4.4.6-6.4 2.6-7 7-.6-4.4-2.6-6.4-7-7 4.4-.6 6.4-2.6 7-7Z" /><path d="M19 16c.2 1.5.9 2.2 2.4 2.4-1.5.2-2.2.9-2.4 2.4-.2-1.5-.9-2.2-2.4-2.4 1.5-.2 2.2-.9 2.4-2.4Z" />
            @break
        @case('lotus')
            <path d="M12 19c-2.5-2-3.5-4.5-3.5-7S10 7 12 5c2 2 3.5 4.5 3.5 7s-1 5-3.5 7Z" /><path d="M8.7 10.5C6.5 9.8 4.5 10 3 11c.5 4.5 4.5 8 9 8M15.3 10.5c2.2-.7 4.2-.5 5.7.5-.5 4.5-4.5 8-9 8" />
            @break
        @case('leaf')
            <path d="M5 19c0-8 5-13 15-14-1 10-6 15-14 15" /><path d="M5 19 14 10" />
            @break
        @case('phone')
            <path d="M5 4h3.5l1.5 4-2 1.5a11 11 0 0 0 6.5 6.5l1.5-2 4 1.5V19a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z" />
            @break
        @case('map-pin')
            <path d="M12 21s-6.5-6-6.5-11a6.5 6.5 0 0 1 13 0c0 5-6.5 11-6.5 11Z" /><circle cx="12" cy="10" r="2.3" />
            @break
        @case('mail')
            <rect x="3" y="5.5" width="18" height="13" rx="1.5" /><path d="m3.5 6.5 8.5 7 8.5-7" />
            @break
        @case('clock')
            <circle cx="12" cy="12" r="8.5" /><path d="M12 7.5V12l3 2" />
            @break
        @case('instagram')
            <rect x="3.5" y="3.5" width="17" height="17" rx="5" /><circle cx="12" cy="12" r="4" /><path d="M17 7h.01" />
            @break
        @case('facebook')
            <path d="M14.5 21v-7.5h2.5l.5-3h-3V8.8c0-.9.4-1.6 1.7-1.6h1.4V4.6a17 17 0 0 0-2.3-.1c-2.4 0-3.8 1.4-3.8 4V10.5H9v3h2.5V21" />
            @break
        @case('heart')
            <path d="M12 20s-7.5-4.6-7.5-10A4.3 4.3 0 0 1 12 7.4 4.3 4.3 0 0 1 19.5 10c0 5.4-7.5 10-7.5 10Z" />
            @break
        @case('arrow-right')
            <path d="M5 12h14M13 6l6 6-6 6" />
            @break
        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16" />
            @break
        @case('close')
            <path d="M6 6l12 12M18 6 6 18" />
            @break
    @endswitch
</svg>
