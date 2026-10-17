<x-layouts::app :title="__('site.nav.gallery')">
    <x-page-header eyebrow="Нашата работа" :title="__('site.nav.gallery')">
        @if ($images->isEmpty())
            Наскоро тука ќе ги најдете фотографиите од салонот и нашите трансформации.
        @else
            Фотографии од салонот и нашите трансформации.
        @endif
    </x-page-header>

    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        @if ($images->isNotEmpty())
            <ul class="columns-2 gap-4 sm:gap-6 md:columns-3 lg:columns-4">
                @foreach ($images as $image)
                    <li class="mb-4 break-inside-avoid sm:mb-6">
                        <img src="{{ $image->url() }}" alt="{{ $image->alt }}" loading="lazy" class="w-full rounded-sm border border-gold-500/25">
                    </li>
                @endforeach
            </ul>
        @else
            <ul class="grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-3 lg:grid-cols-4">
                @foreach (range(1, 8) as $index)
                    <li @class(['md:row-span-2' => in_array($index, [2, 5])])>
                        <div class="bg-marble flex size-full min-h-48 flex-col items-center justify-center gap-3 rounded-sm border border-dashed border-gold-500/35">
                            <x-icon name="camera" class="size-10 text-gold-400/60" />
                            <span class="text-[0.6rem] font-semibold uppercase tracking-[0.25em] text-gold-300/60">Фотографија наскоро</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <x-cta-band />
</x-layouts::app>
