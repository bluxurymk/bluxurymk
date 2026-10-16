@props(['booking'])

<dl {{ $attributes->class(['grid gap-x-8 gap-y-5 sm:grid-cols-2']) }}>
    <div>
        <dt class="text-xs font-semibold uppercase tracking-[0.25em] text-gold-400">{{ __('booking.summary.service') }}</dt>
        <dd class="mt-1 font-serif text-2xl text-beige">{{ $booking->service->name }}</dd>
    </div>
    @if ($booking->stylist)
        <div>
            <dt class="text-xs font-semibold uppercase tracking-[0.25em] text-gold-400">{{ __('booking.summary.stylist') }}</dt>
            <dd class="mt-1 font-serif text-2xl text-beige">{{ $booking->stylist->name }}</dd>
        </div>
    @endif
    <div>
        <dt class="text-xs font-semibold uppercase tracking-[0.25em] text-gold-400">{{ __('booking.summary.when') }}</dt>
        <dd class="mt-1 font-serif text-2xl text-beige">
            {{ $booking->starts_at->locale(app()->getLocale())->isoFormat('dddd, D MMMM YYYY') }}, {{ $booking->starts_at->format('H:i') }}
        </dd>
    </div>
    <div>
        <dt class="text-xs font-semibold uppercase tracking-[0.25em] text-gold-400">{{ __('booking.summary.status') }}</dt>
        <dd class="mt-1 font-serif text-2xl text-beige">{{ $booking->status->label() }}</dd>
    </div>
</dl>
