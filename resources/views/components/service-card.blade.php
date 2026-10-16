@props(['service'])

<article {{ $attributes->class(['group relative flex flex-col items-center gap-4 rounded-sm border border-gold-500/20 bg-marble-900/70 p-8 text-center transition duration-300 hover:-translate-y-1 hover:border-gold-500/60 hover:shadow-xl hover:shadow-gold-600/10']) }}>
    <x-icon-circle :name="$service->icon" class="transition group-hover:shadow-[inset_0_0_18px_rgb(201_161_91/0.35)]" />
    <h3 class="font-serif text-2xl font-semibold uppercase tracking-wide text-gold-300">{{ $service->name }}</h3>
    <p class="text-xs font-medium uppercase tracking-[0.18em] text-beige/70">{{ $service->subtitle }}</p>
    <a href="{{ route('services.show', $service) }}" class="mt-auto inline-flex items-center gap-2 pt-2 text-xs font-semibold uppercase tracking-[0.2em] text-gold-400 after:absolute after:inset-0 hover:text-gold-300">
        {{ __('site.cta.read_more') }}
        <x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-1" />
        <span class="sr-only">— {{ $service->name }}</span>
    </a>
</article>
