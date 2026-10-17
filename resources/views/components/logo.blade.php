{{-- Salon logo, drawn after the flyer: a serif "B" crossed by a hair-strand swoosh, then LUXURY / HAIR SALON. Replace with the original file when the salon supplies it. --}}
@props(['size' => 'sm'])

@php
    $gradientId = 'logo-gold-'.\Illuminate\Support\Str::random(6);
@endphp

<span {{ $attributes->class([
    'inline-flex items-center leading-none',
    'gap-3' => $size === 'sm',
    'flex-col gap-3' => $size === 'lg',
]) }}>
    <svg viewBox="0 0 100 112" @class(['shrink-0 overflow-visible', 'h-12 w-auto' => $size === 'sm', 'h-36 w-auto sm:h-44' => $size === 'lg']) aria-hidden="true">
        <defs>
            <linearGradient id="{{ $gradientId }}" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#e8cf95" />
                <stop offset="0.35" stop-color="#c9a15b" />
                <stop offset="0.6" stop-color="#f3e2b5" />
                <stop offset="1" stop-color="#a67c3d" />
            </linearGradient>
        </defs>
        <text x="52" y="96" text-anchor="middle" font-family="var(--font-cormorant), Georgia, serif" font-size="120" font-weight="600" fill="url(#{{ $gradientId }})">B</text>
        {{-- Hair strand: a tapered sweep with a thin echo, outlined in the background colour so it reads on top of the B. --}}
        <path d="M0 96 C 36 94, 64 56, 100 4 C 74 46, 44 84, 0 96 Z" fill="url(#{{ $gradientId }})" stroke="#0e0c0a" stroke-width="2.5" paint-order="stroke" />
        <path d="M8 106 C 46 102, 74 66, 98 26" fill="none" stroke="url(#{{ $gradientId }})" stroke-width="1.4" stroke-linecap="round" />
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
