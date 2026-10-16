{{-- Circular/oval gold frame. Shows a marble placeholder until real salon photos are added. --}}
@props(['src' => null, 'alt' => '', 'shape' => 'circle'])

<div {{ $attributes->class([
    'relative overflow-hidden border-2 border-gold-500/80 p-1.5 shadow-2xl shadow-black/60',
    'aspect-square rounded-full' => $shape === 'circle',
    'aspect-[3/4] rounded-[50%]' => $shape === 'oval',
]) }}>
    <div class="absolute inset-1.5 rounded-[inherit] border border-gold-300/30" aria-hidden="true"></div>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy" class="size-full rounded-[inherit] object-cover">
    @else
        <div class="bg-marble flex size-full items-center justify-center rounded-[inherit]" role="img" aria-label="{{ $alt }}">
            <span class="text-gold-gradient font-serif text-6xl font-semibold italic opacity-60" aria-hidden="true">B</span>
        </div>
    @endif
</div>
