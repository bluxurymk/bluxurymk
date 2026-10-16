{{-- Deposit rules: paid in person, in cash, at the salon. Pass a booking once its deadline is known. --}}
@props(['booking' => null])

@php
    $amount = number_format($booking?->deposit_amount ?? config('salon.booking.deposit_amount'), 0, ',', '.');
    $deadline = $booking?->deposit_due_at?->locale(app()->getLocale())->isoFormat('dddd, D MMMM, HH:mm');
@endphp

<div {{ $attributes->class(['flex gap-4 rounded-sm border border-gold-500/40 bg-gold-500/5 p-5']) }}>
    <x-icon-circle name="sparkle" size="sm" />
    <div class="flex flex-col gap-2 text-sm leading-relaxed text-beige/85">
        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-gold-300">{{ __('booking.deposit.title') }}</p>
        @if ($deadline)
            <p class="font-medium text-beige">{{ __('booking.deposit.rule_short', ['amount' => $amount, 'deadline' => $deadline]) }}</p>
            <p>{{ __('booking.deposit.auto_cancel', ['deadline' => $deadline]) }}</p>
        @else
            <p>{{ __('booking.deposit.rule', ['amount' => $amount, 'hours' => config('salon.booking.deposit_hours')]) }}</p>
        @endif
    </div>
</div>
