{{-- One step of the booking wizard: collapsed with a summary when done, expanded while active. --}}
@props(['number', 'title', 'done' => false, 'summary' => null, 'changeUrl' => null])

<section {{ $attributes->class([
    'rounded-sm border bg-marble-900/70',
    'border-gold-500/20' => $done,
    'scroll-mt-28 border-gold-500/50 shadow-xl shadow-gold-600/5' => ! $done,
]) }} @unless ($done) id="aktiven-cekor" @endunless aria-labelledby="step-{{ $number }}-title">
    <header class="flex items-center gap-4 p-5 sm:p-6">
        <span @class([
            'inline-flex size-9 shrink-0 items-center justify-center rounded-full border text-sm font-semibold',
            'border-gold-500 bg-gold-500 text-marble-950' => $done,
            'border-gold-500/70 text-gold-300' => ! $done,
        ])>
            @if ($done)
                <x-icon name="check" class="size-5" />
                <span class="sr-only">{{ $number }}</span>
            @else
                {{ $number }}
            @endif
        </span>

        <div class="min-w-0 flex-1">
            <h2 id="step-{{ $number }}-title" class="text-xs font-semibold uppercase tracking-[0.25em] text-gold-400">{{ $title }}</h2>
            @if ($done && $summary)
                <p class="mt-1 truncate font-serif text-xl text-beige">{{ $summary }}</p>
            @endif
        </div>

        @if ($done && $changeUrl)
            <a href="{{ $changeUrl }}" class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-400 underline-offset-4 hover:text-gold-300 hover:underline">
                {{ __('booking.change') }}<span class="sr-only">: {{ $title }}</span>
            </a>
        @endif
    </header>

    @unless ($done)
        <div class="border-t border-gold-500/15 p-5 sm:p-6">
            {{ $slot }}
        </div>
    @endunless
</section>
