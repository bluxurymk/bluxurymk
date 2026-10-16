@props(['eyebrow' => null, 'title'])

<section class="bg-marble border-b border-gold-500/20">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
        <x-section-title :eyebrow="$eyebrow" :title="$title" as="h1">{{ $slot }}</x-section-title>
    </div>
</section>
