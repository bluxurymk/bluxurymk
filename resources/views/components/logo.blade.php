{{-- Temporary text logo until the salon supplies the original logo file. --}}
@props(['size' => 'sm'])

<span {{ $attributes->class([
    'inline-flex items-center font-serif leading-none',
    'gap-2.5' => $size === 'sm',
    'flex-col gap-1' => $size === 'lg',
]) }}>
    <span @class([
        'text-gold-gradient font-semibold italic', 'shimmer' => $size === 'lg',
        'text-4xl' => $size === 'sm',
        'text-8xl sm:text-9xl' => $size === 'lg',
    ])>B</span>
    <span @class(['flex flex-col', 'items-center gap-2' => $size === 'lg', 'gap-1' => $size === 'sm'])>
        <span @class([
            'text-gold-gradient font-semibold tracking-[0.18em]', 'shimmer' => $size === 'lg',
            'text-lg' => $size === 'sm',
            'text-4xl sm:text-5xl' => $size === 'lg',
        ])>LUXURY</span>
        <span @class([
            'font-sans font-medium tracking-[0.35em] text-gold-300/90',
            'text-[0.55rem]' => $size === 'sm',
            'text-xs sm:text-sm' => $size === 'lg',
        ])>HAIR SALON</span>
    </span>
</span>
