<x-layouts::app :title="__('booking.manage.title')">
    <x-page-header :eyebrow="$booking->customer_name" :title="__('booking.manage.title')" />

    <section class="mx-auto flex max-w-3xl flex-col gap-8 px-4 py-16 sm:px-6 lg:px-8">
        @session('status')
            <p class="rounded-sm border border-gold-500/50 bg-gold-500/10 p-4 text-sm text-gold-300" role="status">{{ $value }}</p>
        @endsession

        @error('booking')
            <p class="rounded-sm border border-red-400/50 bg-red-500/10 p-4 text-sm text-red-200" role="alert">{{ $message }}</p>
        @enderror

        <div class="rounded-sm border border-gold-500/30 bg-marble-900/70 p-6 sm:p-8">
            <x-booking-summary :booking="$booking" />
        </div>

        @if ($booking->status === \App\Enums\BookingStatus::PendingDeposit)
            <x-deposit-notice :booking="$booking" />
        @elseif ($booking->status === \App\Enums\BookingStatus::Confirmed)
            <p class="flex items-center gap-3 text-gold-300"><x-icon name="check" class="size-5" /> {{ __('booking.manage.deposit_paid') }}</p>
        @endif

        @if ($booking->isCancellableByCustomer())
            <form method="POST" action="{{ route('booking.destroy', $booking) }}" x-data x-on:submit="if (! confirm(@js(__('booking.manage.cancel_confirm')))) $event.preventDefault()">
                @csrf
                @method('DELETE')
                <x-button variant="outline">{{ __('booking.manage.cancel') }}</x-button>
            </form>
        @endif
    </section>
</x-layouts::app>
