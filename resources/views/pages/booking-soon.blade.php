{{-- Temporary placeholder. Replaced by the booking flow in Phase 2. --}}
<x-layouts::app :title="__('site.cta.book')">
    <x-page-header eyebrow="Наскоро" :title="__('site.cta.book')">
        Онлајн резервацијата наскоро ќе биде достапна. Дотогаш, закажете термин по телефон.
    </x-page-header>

    <section class="flex justify-center px-4 py-20">
        <x-button variant="outline" :href="'tel:'.str_replace(' ', '', config('salon.phone'))">
            <x-icon name="phone" class="size-4" /> {{ config('salon.phone') }}
        </x-button>
    </section>
</x-layouts::app>
