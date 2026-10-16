@props(['href', 'variant' => 'gold'])

<a href="{{ $href }}" {{ $attributes->class([
    'inline-flex items-center justify-center gap-2 rounded-full px-6 py-3 text-xs font-semibold uppercase tracking-[0.2em] transition duration-300',
    'bg-gold-gradient text-marble-950 shadow-lg shadow-gold-600/20 hover:brightness-110 hover:shadow-gold-500/40' => $variant === 'gold',
    'border border-gold-500/70 text-gold-300 hover:bg-gold-500/10 hover:text-gold-300' => $variant === 'outline',
]) }}>{{ $slot }}</a>
