<x-layouts::app :title="__('site.nav.about')">
    <x-page-header eyebrow="Добредојдовте" :title="__('site.nav.about')" />

    <section class="mx-auto grid max-w-6xl items-center gap-12 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:px-8">
        <x-photo-frame image="about-salon" shape="oval" alt="Ентериер на салонот" placeholder="Фото: салонот однатре" class="mx-auto w-full max-w-sm" />
        <div class="flex flex-col gap-5 leading-relaxed text-beige/85">
            <p class="font-script text-3xl text-gold-300">{{ __('site.tagline') }}</p>
            <p>
                B Luxury Hair Salon е простор каде стилот, квалитетот и убавината се среќаваат. Во срцето на Куманово создадовме луксузна и топла атмосфера во која секоја клиентка добива внимание, совет и резултат со кој ќе се чувствува прекрасно.
            </p>
            <p>
                Работиме со професионални производи и ги следиме најновите техники — од кератин и ламинација до балајаж и Airtouch — за вашата коса да изгледа здраво, сјајно и неповторливо.
            </p>
        </div>
    </section>

    <section class="bg-marble-light text-marble-900">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <x-section-title eyebrow="Нашите вредности" :title="__('site.tagline_secondary')" :dark="false" />
            <div class="mt-14 grid gap-10 md:grid-cols-3">
                @foreach ([
                    ['icon' => 'scissors', 'title' => 'Стил', 'text' => 'Следиме трендови, но секогаш создаваме изглед кој е вистински ваш.'],
                    ['icon' => 'sparkle', 'title' => 'Квалитет', 'text' => 'Користиме само проверени професионални производи и техники.'],
                    ['icon' => 'heart', 'title' => 'Убавина', 'text' => 'Вашето задоволство и самодоверба се нашата најголема награда.'],
                ] as $value)
                    <div class="flex flex-col items-center gap-4 text-center">
                        <x-icon-circle :name="$value['icon']" class="text-gold-600! border-gold-600/70!" />
                        <h3 class="font-serif text-2xl font-semibold uppercase tracking-wide">{{ $value['title'] }}</h3>
                        <p class="max-w-xs text-marble-700">{{ $value['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <x-section-title eyebrow="Запознајте нè" title="Нашиот тим">
            @if ($stylists->isEmpty())
                Наскоро ќе ги претставиме нашите стилисти.
            @endif
        </x-section-title>
        @if ($stylists->isNotEmpty())
            <ul class="mt-14 flex flex-wrap justify-center gap-x-10 gap-y-14">
                @foreach ($stylists as $stylist)
                    <li class="flex w-64 flex-col items-center gap-4 text-center">
                        <x-photo-frame :src="$stylist->photoUrl()" :alt="$stylist->name" placeholder="Фото: {{ $stylist->name }}" class="w-48" />
                        <p class="font-serif text-2xl text-gold-300">{{ $stylist->name }}</p>
                        @if ($stylist->bio)
                            <p class="text-sm leading-relaxed text-beige/75">{{ $stylist->bio }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <x-cta-band />
</x-layouts::app>
