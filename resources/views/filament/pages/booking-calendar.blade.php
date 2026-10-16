<x-filament-panels::page>
    @php
        $weekStart = $this->weekStart();
        $locale = app()->getLocale();
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold text-gray-950 dark:text-white">
            {{ $weekStart->locale($locale)->isoFormat('D MMMM') }} – {{ $weekStart->endOfWeek()->locale($locale)->isoFormat('D MMMM YYYY') }}
        </h2>

        <div class="flex gap-2">
            <x-filament::button color="gray" icon="heroicon-m-chevron-left" wire:click="previousWeek">Претходна</x-filament::button>
            <x-filament::button color="gray" wire:click="thisWeek">Оваа недела</x-filament::button>
            <x-filament::button color="gray" icon="heroicon-m-chevron-right" icon-position="after" wire:click="nextWeek">Следна</x-filament::button>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-7">
        @foreach ($this->days as $day)
            <section @class([
                'flex flex-col gap-2 rounded-xl p-3 ring-1',
                'bg-primary-50 ring-primary-400 dark:bg-primary-500/10' => $day['date']->isToday(),
                'bg-white ring-gray-950/5 dark:bg-white/5 dark:ring-white/10' => ! $day['date']->isToday(),
            ])>
                <h3 class="text-sm font-semibold capitalize text-gray-950 dark:text-white">
                    {{ $day['date']->locale($locale)->isoFormat('dddd') }}
                    <span class="font-normal text-gray-500 dark:text-gray-400">{{ $day['date']->format('d.m.') }}</span>
                </h3>

                @forelse ($day['bookings'] as $booking)
                    <a href="{{ \App\Filament\Resources\Bookings\BookingResource::getUrl('edit', ['record' => $booking]) }}"
                       class="flex flex-col gap-1 rounded-lg border border-gray-200 p-2 text-xs transition hover:border-primary-400 hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5">
                        <span class="flex items-center justify-between gap-2">
                            <span class="font-semibold text-gray-950 dark:text-white">{{ $booking->starts_at->format('H:i') }}–{{ $booking->ends_at->format('H:i') }}</span>
                            <x-filament::badge size="sm" :color="$booking->status->getColor()">{{ $booking->status->label() }}</x-filament::badge>
                        </span>
                        <span class="text-gray-950 dark:text-white">{{ $booking->customer_name }}</span>
                        <span class="text-gray-500 dark:text-gray-400">{{ $booking->service->name }}@if ($booking->stylist) · {{ $booking->stylist->name }}@endif</span>
                    </a>
                @empty
                    <p class="text-xs text-gray-400 dark:text-gray-500">Нема термини</p>
                @endforelse
            </section>
        @endforeach
    </div>
</x-filament-panels::page>
