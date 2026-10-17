{{-- Circular/oval gold frame. Pass `src`, or an `image` name to pick up public/images/site/{name}.webp|jpg|jpeg|png once the salon's photo is added; until then a labelled placeholder shows. --}}
@props(['src' => null, 'image' => null, 'alt' => '', 'shape' => 'circle', 'placeholder' => null])

@php
    if ($src === null && $image !== null) {
        $file = collect(['webp', 'jpg', 'jpeg', 'png'])
            ->map(fn (string $extension): string => "images/site/{$image}.{$extension}")
            ->first(fn (string $path): bool => is_file(public_path($path)));
        $src = $file ? asset($file) : null;
    }
@endphp

<div {{ $attributes->class([
    'relative overflow-hidden border-2 border-gold-500/80 p-1.5 shadow-2xl shadow-black/60',
    'aspect-square rounded-full' => $shape === 'circle',
    'aspect-[3/4] rounded-[50%]' => $shape === 'oval',
]) }}>
    <div class="absolute inset-1.5 rounded-[inherit] border border-gold-300/30" aria-hidden="true"></div>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy" class="size-full rounded-[inherit] object-cover">
    @else
        <div class="bg-marble @container flex size-full flex-col items-center justify-center gap-3 rounded-[inherit] p-4 text-center" role="img" aria-label="{{ $alt }}">
            <x-icon name="camera" class="size-8 text-gold-400/70 @[9rem]:size-12" />
            <span class="hidden max-w-[70%] text-[0.6rem] font-semibold uppercase leading-relaxed tracking-[0.25em] text-gold-300/70 @[9rem]:block" aria-hidden="true">{{ $placeholder ?? $alt }}</span>
        </div>
    @endif
</div>
