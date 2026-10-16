<x-layouts::app :title="__('site.nav.gallery')">
    <x-page-header eyebrow="Нашата работа" :title="__('site.nav.gallery')">
        Наскоро тука ќе ги најдете фотографиите од салонот и нашите трансформации.
    </x-page-header>

    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <ul class="grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-3 lg:grid-cols-4">
            @foreach (range(1, 8) as $index)
                <li @class(['md:row-span-2' => in_array($index, [2, 5])])>
                    <div class="bg-marble flex size-full min-h-48 items-center justify-center rounded-sm border border-gold-500/25">
                        <span class="text-gold-gradient font-serif text-5xl italic opacity-40" aria-hidden="true">B</span>
                        <span class="sr-only">Фотографија наскоро</span>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    <x-cta-band />
</x-layouts::app>
