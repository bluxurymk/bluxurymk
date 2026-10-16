@props(['eyebrow' => null, 'title', 'as' => 'h2', 'dark' => true])

<div {{ $attributes->class(['flex flex-col items-center gap-4 text-center']) }}>
    @if ($eyebrow)
        <p @class(['font-script text-2xl', 'text-gold-300' => $dark, 'text-gold-600' => ! $dark])>{{ $eyebrow }}</p>
    @endif
    <{{ $as }} @class([
        'font-serif text-4xl font-semibold uppercase tracking-wide sm:text-5xl',
        'text-gold-gradient shimmer' => $dark,
        'text-marble-900' => ! $dark,
    ])>{{ $title }}</{{ $as }}>
    <x-gold-divider />
    @if ($slot->hasActualContent())
        <p @class(['max-w-2xl text-base leading-relaxed', 'text-beige/80' => $dark, 'text-marble-700' => ! $dark])>{{ $slot }}</p>
    @endif
</div>
