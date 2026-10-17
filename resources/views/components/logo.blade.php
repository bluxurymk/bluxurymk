{{-- Salon logo, drawn after the flyer: a gold serif "B" cut by a sweep of hair strands, then LUXURY / HAIR SALON. Replace with the original file when the salon supplies it. --}}
@props(['size' => 'sm'])

@php
    $id = 'logo-'.\Illuminate\Support\Str::random(6);
    $swoosh = 'M4 124 C 40 120, 76 76, 116 8';
@endphp

<span {{ $attributes->class([
    'inline-flex items-center leading-none',
    'gap-3' => $size === 'sm',
    'flex-col gap-3' => $size === 'lg',
]) }}>
    <svg viewBox="0 0 120 140" @class(['shrink-0 overflow-visible', 'h-12 w-auto' => $size === 'sm', 'h-40 w-auto sm:h-48' => $size === 'lg']) aria-hidden="true">
        <defs>
            <linearGradient id="{{ $id }}-gold" gradientUnits="userSpaceOnUse" x1="10" y1="0" x2="80" y2="140">
                <stop offset="0" stop-color="#f7e9c4" />
                <stop offset="0.22" stop-color="#e2c27f" />
                <stop offset="0.48" stop-color="#a67c3d" />
                <stop offset="0.66" stop-color="#ecd296" />
                <stop offset="0.85" stop-color="#c9a15b" />
                <stop offset="1" stop-color="#7d5a2a" />
            </linearGradient>
            {{-- Cuts a channel through the B where the hair strand passes. --}}
            <mask id="{{ $id }}-cut" maskUnits="userSpaceOnUse" x="-10" y="-10" width="140" height="160">
                <rect x="-10" y="-10" width="140" height="160" fill="#fff" />
                <path d="{{ $swoosh }}" fill="none" stroke="#000" stroke-width="11" stroke-linecap="round" />
            </mask>
        </defs>
        <text x="62" y="114" text-anchor="middle" font-family="var(--font-cormorant), Georgia, serif" font-size="150" font-weight="700" fill="url(#{{ $id }}-gold)" mask="url(#{{ $id }}-cut)">B</text>
        {{-- Three tapered hair strands sweeping up through the B, with a sparkle at the tip. --}}
        <path d="{{ $swoosh }} C 80 58, 42 104, 4 124 Z" fill="url(#{{ $id }}-gold)" />
        <path d="M16 132 C 52 126, 86 84, 112 34 C 88 74, 56 112, 16 132 Z" fill="url(#{{ $id }}-gold)" opacity="0.9" />
        <path d="M-2 114 C 28 108, 56 74, 88 30 C 62 66, 38 98, -2 114 Z" fill="url(#{{ $id }}-gold)" opacity="0.75" />
        <path d="M116 0 Q117.2 6.8 124 8 Q117.2 9.2 116 16 Q114.8 9.2 108 8 Q114.8 6.8 116 0 Z" fill="#f7e9c4" />
    </svg>

    <span @class(['flex flex-col', 'items-center gap-2.5' => $size === 'lg', 'gap-1.5' => $size === 'sm'])>
        <span @class([
            'text-gold-gradient font-serif font-semibold',
            'shimmer text-5xl tracking-[0.2em] sm:text-6xl' => $size === 'lg',
            'text-xl tracking-[0.18em]' => $size === 'sm',
        ])>LUXURY</span>
        <span @class(['flex items-center', 'gap-3' => $size === 'lg', 'gap-2' => $size === 'sm'])>
            <span @class(['h-px bg-linear-to-r from-transparent to-gold-500', 'w-10' => $size === 'lg', 'w-3' => $size === 'sm'])></span>
            <span @class([
                'font-sans font-medium tracking-[0.35em] text-gold-300/90',
                'text-xs sm:text-sm' => $size === 'lg',
                'text-[0.5rem]' => $size === 'sm',
            ])>HAIR SALON</span>
            <span @class(['h-px bg-linear-to-l from-transparent to-gold-500', 'w-10' => $size === 'lg', 'w-3' => $size === 'sm'])></span>
        </span>
    </span>
</span>
