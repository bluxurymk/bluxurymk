@php
    $locale = app()->getLocale();
    $stepUrl = fn (array $params): string => route('booking.create', array_filter($params, fn ($value) => $value !== null)).'#aktiven-cekor';
    $chosen = ['service' => $service?->slug, 'stylist' => $stylistParam];
    $showStylistStep = $service && $stylists->count() > 1;
    $stepNumbers = ['service' => 1, 'stylist' => 2, 'date' => $showStylistStep ? 3 : 2];
    $stepNumbers['time'] = $stepNumbers['date'] + 1;
    $stepNumbers['details'] = $stepNumbers['time'] + 1;
    $choiceClasses = 'flex h-full items-center gap-4 rounded-sm border border-gold-500/25 bg-marble-950/60 p-4 transition hover:border-gold-400 hover:bg-gold-500/5 focus-visible:border-gold-400';
    $inputClasses = 'w-full rounded-sm border border-gold-500/30 bg-marble-950/70 px-4 py-3 text-beige placeholder:text-beige/35 transition focus:border-gold-400 focus:outline-none';
@endphp

<x-layouts::app :title="__('booking.title')">
    <x-page-header :eyebrow="__('booking.eyebrow')" :title="__('booking.title')">{{ __('booking.intro') }}</x-page-header>

    <div class="mx-auto flex max-w-4xl flex-col gap-5 px-4 py-16 sm:px-6 lg:px-8">
        @error('time')
            <p class="rounded-sm border border-red-400/50 bg-red-500/10 p-4 text-sm text-red-200" role="alert">{{ $message }}</p>
        @enderror

        {{-- Service --}}
        <x-booking-step :number="$stepNumbers['service']" :title="__('booking.steps.service')" :done="$service !== null" :summary="$service?->name" :change-url="$stepUrl([])">
            <p class="mb-4 text-sm text-beige/70">{{ __('booking.choose_service') }}</p>
            <ul class="grid gap-3 sm:grid-cols-2">
                @foreach ($services as $option)
                    <li>
                        <a href="{{ $stepUrl(['service' => $option->slug]) }}" class="{{ $choiceClasses }}">
                            <x-icon-circle :name="$option->icon" size="sm" />
                            <span class="flex flex-col">
                                <span class="font-serif text-xl text-beige">{{ $option->name }}</span>
                                <span class="text-xs uppercase tracking-[0.15em] text-beige/60">~{{ $option->duration_min }} {{ __('site.labels.minutes') }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </x-booking-step>

        @if ($service && $stylists->isEmpty())
            <p class="rounded-sm border border-gold-500/30 p-5 text-beige/80">{{ __('booking.no_stylists', ['phone' => config('salon.phone')]) }}</p>
        @endif

        {{-- Stylist (skipped when only one stylist performs the service) --}}
        @if ($showStylistStep)
            <x-booking-step :number="$stepNumbers['stylist']" :title="__('booking.steps.stylist')" :done="$hasChosenStylist" :summary="$stylist?->name ?? __('booking.any_stylist')" :change-url="$stepUrl(['service' => $service->slug])">
                <p class="mb-4 text-sm text-beige/70">{{ __('booking.choose_stylist') }}</p>
                <ul class="grid gap-3 sm:grid-cols-2">
                    <li>
                        <a href="{{ $stepUrl(['service' => $service->slug, 'stylist' => 'any']) }}" class="{{ $choiceClasses }}">
                            <x-icon-circle name="sparkle" size="sm" />
                            <span class="flex flex-col">
                                <span class="font-serif text-xl text-beige">{{ __('booking.any_stylist') }}</span>
                                <span class="text-xs uppercase tracking-[0.15em] text-beige/60">{{ __('booking.any_stylist_hint') }}</span>
                            </span>
                        </a>
                    </li>
                    @foreach ($stylists as $option)
                        <li>
                            <a href="{{ $stepUrl(['service' => $service->slug, 'stylist' => $option->id]) }}" class="{{ $choiceClasses }}">
                                <x-icon-circle name="user" size="sm" />
                                <span class="font-serif text-xl text-beige">{{ $option->name }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </x-booking-step>
        @endif

        {{-- Date --}}
        @if ($hasChosenStylist)
            <x-booking-step :number="$stepNumbers['date']" :title="__('booking.steps.date')" :done="$date !== null" :summary="$date?->locale($locale)->isoFormat('dddd, D MMMM')" :change-url="$stepUrl($chosen)">
                <p class="mb-4 text-sm text-beige/70">{{ __('booking.choose_date') }}</p>

                @if ($dates->isEmpty())
                    <p class="text-beige/80">{{ __('booking.no_dates') }}</p>
                @else
                    <ul class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-7">
                        @foreach ($dates as $option)
                            <li>
                                <a href="{{ $stepUrl([...$chosen, 'date' => $option->toDateString()]) }}" class="{{ $choiceClasses }} flex-col justify-center gap-1! text-center">
                                    <span class="text-xs uppercase tracking-[0.15em] text-gold-400">{{ $option->locale($locale)->isoFormat('ddd') }}</span>
                                    <span class="font-serif text-3xl leading-none text-beige">{{ $option->day }}</span>
                                    <span class="text-xs text-beige/60">{{ $option->locale($locale)->isoFormat('MMM') }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="mt-5 flex justify-between gap-4 text-xs font-semibold uppercase tracking-[0.2em]">
                    @if ($previousWindow)
                        <a href="{{ $stepUrl([...$chosen, 'from' => $previousWindow->toDateString()]) }}" class="inline-flex items-center gap-2 text-gold-400 hover:text-gold-300">
                            <x-icon name="arrow-left" class="size-4" /> {{ __('booking.earlier') }}
                        </a>
                    @else
                        <span></span>
                    @endif
                    @if ($nextWindow)
                        <a href="{{ $stepUrl([...$chosen, 'from' => $nextWindow->toDateString()]) }}" class="inline-flex items-center gap-2 text-gold-400 hover:text-gold-300">
                            {{ __('booking.later') }} <x-icon name="arrow-right" class="size-4" />
                        </a>
                    @endif
                </div>
            </x-booking-step>
        @endif

        {{-- Time --}}
        @if ($date)
            <x-booking-step :number="$stepNumbers['time']" :title="__('booking.steps.time')" :done="$time !== null" :summary="$time" :change-url="$stepUrl([...$chosen, 'date' => $date->toDateString()])">
                @if ($slots->isEmpty())
                    <p class="text-beige/80">{{ __('booking.no_slots') }}</p>
                @else
                    <p class="mb-4 text-sm text-beige/70">{{ __('booking.choose_time') }}</p>
                    <ul class="grid grid-cols-3 gap-3 sm:grid-cols-5 md:grid-cols-6">
                        @foreach ($slots->keys() as $option)
                            <li>
                                <a href="{{ $stepUrl([...$chosen, 'date' => $date->toDateString(), 'time' => $option]) }}" class="{{ $choiceClasses }} justify-center p-3! font-serif text-xl text-beige">{{ $option }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-booking-step>
        @endif

        {{-- Customer details --}}
        @if ($time)
            <x-booking-step :number="$stepNumbers['details']" :title="__('booking.steps.details')">
                <form method="POST" action="{{ route('booking.store') }}" class="flex flex-col gap-5">
                    @csrf
                    <input type="hidden" name="service" value="{{ $service->slug }}">
                    <input type="hidden" name="stylist" value="{{ $stylist?->id }}">
                    <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                    <input type="hidden" name="time" value="{{ $time }}">

                    <div class="absolute -left-[9999px]" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        @foreach (['customer_name' => ['text', 'name'], 'customer_phone' => ['tel', 'tel'], 'customer_email' => ['email', 'email']] as $field => [$type, $autocomplete])
                            <div @class(['flex flex-col gap-2', 'sm:col-span-2' => $field === 'customer_name'])>
                                <label for="{{ $field }}" class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-300">{{ __('booking.fields.'.$field) }}</label>
                                <input type="{{ $type }}" id="{{ $field }}" name="{{ $field }}" value="{{ old($field) }}" autocomplete="{{ $autocomplete }}" required
                                       @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror
                                       class="{{ $inputClasses }}">
                                @error($field)
                                    <p id="{{ $field }}-error" class="text-sm text-red-300">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach

                        <div class="flex flex-col gap-2 sm:col-span-2">
                            <label for="note" class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-300">{{ __('booking.fields.note') }}</label>
                            <textarea id="note" name="note" rows="3" maxlength="500" class="{{ $inputClasses }}">{{ old('note') }}</textarea>
                            @error('note')
                                <p class="text-sm text-red-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <x-deposit-notice />

                    <div class="flex flex-col gap-2">
                        <label class="flex items-start gap-3 text-sm leading-relaxed text-beige/85">
                            <input type="checkbox" name="consent" value="1" @checked(old('consent')) required class="mt-1 size-4 shrink-0 accent-gold-500">
                            <span>
                                {{ __('booking.fields.consent') }}
                                <a href="{{ route('privacy') }}" target="_blank" class="text-gold-300 underline underline-offset-4 hover:text-gold-400">{{ __('booking.fields.consent_link') }}</a>.
                            </span>
                        </label>
                        @error('consent')
                            <p class="text-sm text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-button class="self-start">{{ __('booking.submit') }}</x-button>
                </form>
            </x-booking-step>
        @endif
    </div>
</x-layouts::app>
