@php
    $sidebarSteps = ['service' => 'scissors', 'stylist' => 'user', 'datetime' => 'calendar', 'details' => 'mail'];
    $choiceClasses = 'flex w-full cursor-pointer items-center gap-4 rounded-sm border p-4 text-left transition hover:border-gold-400 hover:bg-gold-500/5';
    $inputClasses = 'w-full rounded-sm border border-gold-500/30 bg-marble-950/70 px-4 py-3 text-beige placeholder:text-beige/35 transition focus:border-gold-400 focus:outline-none';
    $stepHeadingClasses = 'font-serif text-3xl text-beige sm:text-4xl';
@endphp

<x-layouts::app :title="__('booking.title')">
    <x-page-header :eyebrow="__('booking.eyebrow')" :title="__('booking.title')">{{ __('booking.intro') }}</x-page-header>

    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
        <div x-data="booking(@js($state))" class="grid overflow-hidden rounded-sm border border-gold-500/30 bg-marble-900/70 shadow-2xl shadow-black/50 lg:grid-cols-[17rem_1fr]">

            {{-- Steps menu --}}
            <aside class="bg-marble flex flex-col gap-8 border-b border-gold-500/20 p-5 sm:p-6 lg:border-r lg:border-b-0 lg:p-8">
                <ol class="flex justify-between gap-2 lg:flex-col lg:gap-1">
                    @foreach ($sidebarSteps as $step => $icon)
                        <li @if ($step === 'stylist') x-show="steps.includes('stylist')" x-cloak @endif class="flex-1 lg:flex-none">
                            <button type="button"
                                    @click="open('{{ $step }}')"
                                    :disabled="!canOpen('{{ $step }}')"
                                    :aria-current="step === '{{ $step }}' ? 'step' : null"
                                    class="group flex w-full flex-col items-center gap-2 rounded-sm p-1 text-center transition enabled:cursor-pointer disabled:cursor-not-allowed lg:flex-row lg:gap-4 lg:p-3 lg:text-left"
                                    :class="step === '{{ $step }}' ? 'lg:bg-gold-500/10' : 'enabled:hover:bg-gold-500/5'">
                                <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-full border transition"
                                      :class="step === '{{ $step }}' ? 'border-gold-400 bg-gold-gradient text-marble-950' : (isDone('{{ $step }}') ? 'border-gold-500/70 text-gold-300' : 'border-beige/20 text-beige/40')">
                                    <x-icon :name="$icon" class="size-5" x-show="!isDone('{{ $step }}') || step === '{{ $step }}'" />
                                    <x-icon name="check" class="size-5" x-show="isDone('{{ $step }}') && step !== '{{ $step }}'" x-cloak />
                                </span>
                                <span class="flex min-w-0 flex-col gap-0.5">
                                    <span class="text-[0.6rem] font-semibold uppercase tracking-[0.2em] transition sm:text-xs"
                                          :class="step === '{{ $step }}' || isDone('{{ $step }}') ? 'text-gold-300' : 'text-beige/45'">
                                        {{ __('booking.steps.'.$step) }}
                                    </span>
                                    <span class="hidden truncate font-serif text-lg leading-tight text-beige lg:block"
                                          x-text="{
                                              service: chosenService?.name,
                                              stylist: stylistChosen ? (stylistName ?? @js(__('booking.any_stylist'))) : null,
                                              datetime: time ? `${formatDate(date)}, ${time}` : null,
                                              details: null,
                                          }['{{ $step }}'] ?? @js(__('booking.step_hints.'.$step))"
                                          :class="isDone('{{ $step }}') ? '' : 'text-beige/40! text-base!'">{{ __('booking.step_hints.'.$step) }}</span>
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ol>

                <div class="mt-auto hidden flex-col gap-2 border-t border-gold-500/20 pt-6 text-sm lg:flex">
                    <p class="text-beige/60">{{ __('booking.help') }}</p>
                    <a href="tel:{{ preg_replace('/\s+/', '', config('salon.phone')) }}" class="inline-flex items-center gap-2 font-serif text-xl text-gold-300 hover:text-gold-400">
                        <x-icon name="phone" class="size-5" /> {{ config('salon.phone') }}
                    </a>
                </div>
            </aside>

            {{-- Current step --}}
            <section x-ref="panel" class="flex min-h-[32rem] scroll-mt-28 flex-col gap-6 p-5 sm:p-8">

                {{-- Service --}}
                <div x-show="step === 'service'" class="flex flex-col gap-6">
                    <h2 class="{{ $stepHeadingClasses }}">{{ __('booking.choose_service') }}</h2>
                    <ul class="grid gap-3 sm:grid-cols-2">
                        @foreach ($services as $option)
                            <li>
                                <button type="button" @click="chooseService(@js($option->slug))" class="{{ $choiceClasses }}"
                                        :class="service === @js($option->slug) ? 'border-gold-400 bg-gold-500/10' : 'border-gold-500/25 bg-marble-950/60'">
                                    <x-icon-circle :name="$option->icon" size="sm" />
                                    <span class="flex min-w-0 flex-col">
                                        <span class="font-serif text-xl text-beige">{{ $option->name }}</span>
                                        <span class="inline-flex items-center gap-1.5 text-xs uppercase tracking-[0.15em] text-beige/60">
                                            <x-icon name="clock" class="size-3.5" /> ~{{ $option->duration_min }} {{ __('site.labels.minutes') }}
                                        </span>
                                    </span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    <p x-show="service && stylists.length === 0" x-cloak class="rounded-sm border border-gold-500/30 p-5 text-beige/80">
                        {{ __('booking.no_stylists', ['phone' => config('salon.phone')]) }}
                    </p>
                </div>

                {{-- Stylist (only when more than one stylist performs the service) --}}
                <div x-show="step === 'stylist'" x-cloak class="flex flex-col gap-6">
                    <h2 class="{{ $stepHeadingClasses }}">{{ __('booking.choose_stylist') }}</h2>
                    <ul class="grid gap-3 sm:grid-cols-2">
                        <li>
                            <button type="button" @click="chooseStylist(null)" class="{{ $choiceClasses }}"
                                    :class="stylistChosen && stylist === null ? 'border-gold-400 bg-gold-500/10' : 'border-gold-500/25 bg-marble-950/60'">
                                <x-icon-circle name="sparkle" size="sm" />
                                <span class="flex flex-col">
                                    <span class="font-serif text-xl text-beige">{{ __('booking.any_stylist') }}</span>
                                    <span class="text-xs uppercase tracking-[0.15em] text-beige/60">{{ __('booking.any_stylist_hint') }}</span>
                                </span>
                            </button>
                        </li>
                        <template x-for="option in stylists" :key="option.id">
                            <li>
                                <button type="button" @click="chooseStylist(option.id)" class="{{ $choiceClasses }}"
                                        :class="stylist === option.id ? 'border-gold-400 bg-gold-500/10' : 'border-gold-500/25 bg-marble-950/60'">
                                    <x-icon-circle name="user" size="sm" />
                                    <span class="font-serif text-xl text-beige" x-text="option.name"></span>
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>

                {{-- Date and time --}}
                <div x-show="step === 'datetime'" x-cloak class="flex flex-col gap-6">
                    <h2 class="{{ $stepHeadingClasses }}">{{ __('booking.choose_datetime') }}</h2>

                    @error('time')
                        <p class="rounded-sm border border-red-400/50 bg-red-500/10 p-4 text-sm text-red-200" role="alert">{{ $message }}</p>
                    @enderror
                    <p x-show="loadFailed" class="rounded-sm border border-red-400/50 bg-red-500/10 p-4 text-sm text-red-200" role="alert">
                        {{ __('booking.load_failed', ['phone' => config('salon.phone')]) }}
                    </p>

                    <div class="grid gap-8 md:grid-cols-[minmax(0,26rem)_1fr]">
                        {{-- Calendar --}}
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center justify-between gap-4">
                                <button type="button" @click="shiftMonth(-1)" :disabled="!canGoToPreviousMonth"
                                        class="inline-flex size-10 cursor-pointer items-center justify-center rounded-full border border-gold-500/40 text-gold-300 transition hover:bg-gold-500/10 disabled:cursor-not-allowed disabled:opacity-25">
                                    <x-icon name="arrow-left" class="size-4" /><span class="sr-only">{{ __('booking.previous_month') }}</span>
                                </button>
                                <p class="font-serif text-2xl capitalize text-beige" x-text="monthLabel" aria-live="polite"></p>
                                <button type="button" @click="shiftMonth(1)" :disabled="!canGoToNextMonth"
                                        class="inline-flex size-10 cursor-pointer items-center justify-center rounded-full border border-gold-500/40 text-gold-300 transition hover:bg-gold-500/10 disabled:cursor-not-allowed disabled:opacity-25">
                                    <x-icon name="arrow-right" class="size-4" /><span class="sr-only">{{ __('booking.next_month') }}</span>
                                </button>
                            </div>

                            <div class="grid grid-cols-7 gap-1.5 text-center" :aria-busy="loadingDates">
                                <template x-for="index in 7" :key="'weekday-' + index">
                                    <span class="pb-1 text-[0.65rem] font-semibold uppercase tracking-[0.15em] text-gold-400" x-text="weekday(index - 1)"></span>
                                </template>
                                <template x-for="(cell, index) in calendarDays" :key="cell ? cell.iso : 'blank-' + index">
                                    <div class="flex items-center justify-center">
                                        <button type="button" x-show="cell" @click="chooseDate(cell.iso)" :disabled="!cell?.available"
                                                :aria-pressed="date === cell?.iso"
                                                class="size-10 rounded-full border text-sm transition enabled:cursor-pointer sm:size-11 sm:text-base"
                                                :class="date === cell?.iso
                                                    ? 'border-gold-400 bg-gold-gradient font-semibold text-marble-950'
                                                    : (cell?.available ? 'border-gold-500/40 text-beige hover:border-gold-400 hover:bg-gold-500/10' : 'border-transparent text-beige/25')"
                                                x-text="cell?.day"></button>
                                    </div>
                                </template>
                            </div>

                            <p x-show="loadingDates" class="text-sm text-beige/60">{{ __('booking.loading') }}</p>
                            <p x-show="!loadingDates && !loadFailed && availableDates.length === 0" class="text-sm text-beige/70">{{ __('booking.no_dates') }}</p>
                        </div>

                        {{-- Times --}}
                        <div class="flex flex-col gap-4 border-t border-gold-500/20 pt-6 md:border-t-0 md:border-l md:pt-0 md:pl-8">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-gold-400">{{ __('booking.free_times') }}</p>
                            <p x-show="date" class="font-serif text-xl capitalize text-beige" x-text="formatDate(date)"></p>
                            <p x-show="!date" class="text-sm leading-relaxed text-beige/60">{{ __('booking.pick_date_first') }}</p>
                            <p x-show="date && loadingSlots" class="text-sm text-beige/60">{{ __('booking.loading') }}</p>
                            <p x-show="date && !loadingSlots && !loadFailed && slots.length === 0" class="text-sm text-beige/70">{{ __('booking.no_slots') }}</p>
                            <ul x-show="slots.length > 0" class="grid max-h-80 grid-cols-3 gap-2 overflow-y-auto pr-1 md:grid-cols-2">
                                <template x-for="option in slots" :key="option">
                                    <li>
                                        <button type="button" @click="chooseTime(option)"
                                                class="w-full cursor-pointer rounded-sm border px-2 py-2.5 font-serif text-lg transition"
                                                :class="time === option ? 'border-gold-400 bg-gold-gradient text-marble-950' : 'border-gold-500/30 text-beige hover:border-gold-400 hover:bg-gold-500/10'"
                                                x-text="option"></button>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Customer details --}}
                <div x-show="step === 'details'" x-cloak class="flex flex-col gap-6">
                    <h2 class="{{ $stepHeadingClasses }}">{{ __('booking.fill_details') }}</h2>

                    <dl class="grid gap-4 rounded-sm border border-gold-500/25 bg-marble-950/50 p-5 sm:grid-cols-3">
                        <div>
                            <dt class="text-[0.65rem] font-semibold uppercase tracking-[0.25em] text-gold-400">{{ __('booking.summary.service') }}</dt>
                            <dd class="mt-1 font-serif text-xl text-beige" x-text="chosenService?.name"></dd>
                        </div>
                        <div>
                            <dt class="text-[0.65rem] font-semibold uppercase tracking-[0.25em] text-gold-400">{{ __('booking.summary.when') }}</dt>
                            <dd class="mt-1 font-serif text-xl capitalize text-beige" x-text="`${formatDate(date)}, ${time}`"></dd>
                        </div>
                        <div x-show="needsStylistStep">
                            <dt class="text-[0.65rem] font-semibold uppercase tracking-[0.25em] text-gold-400">{{ __('booking.summary.stylist') }}</dt>
                            <dd class="mt-1 font-serif text-xl text-beige" x-text="stylistName ?? @js(__('booking.any_stylist'))"></dd>
                        </div>
                    </dl>

                    <form method="POST" action="{{ route('booking.store') }}" class="flex flex-col gap-5">
                        @csrf
                        <input type="hidden" name="service" :value="service">
                        <input type="hidden" name="stylist" :value="stylist ?? ''">
                        <input type="hidden" name="date" :value="date">
                        <input type="hidden" name="time" :value="time">

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
                </div>

                {{-- Back --}}
                <div x-show="step !== 'service'" x-cloak class="mt-auto border-t border-gold-500/15 pt-5">
                    <button type="button" @click="back()" class="inline-flex cursor-pointer items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-gold-400 hover:text-gold-300">
                        <x-icon name="arrow-left" class="size-4" /> {{ __('booking.back') }}
                    </button>
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
