<x-mail::message>
# {{ __('emails.greeting', ['name' => $booking->customer_name]) }}

@if ($booking->cancel_reason === \App\Models\Booking::CANCELLED_BY_CUSTOMER)
{{ __('emails.cancelled.by_customer') }}
@elseif ($booking->cancel_reason === \App\Models\Booking::CANCELLED_DEPOSIT_EXPIRED)
{{ __('emails.cancelled.deposit_expired') }}
@else
{{ __('emails.cancelled.by_salon') }}
@if (filled($booking->cancel_reason) && $booking->cancel_reason !== \App\Models\Booking::CANCELLED_BY_SALON)

{{ __('emails.cancelled.reason', ['reason' => $booking->cancel_reason]) }}
@endif
@endif

@include('emails.booking.partials.details')

<x-mail::button :url="route('booking.create')">
{{ __('emails.cancelled.book_again') }}
</x-mail::button>

{{ __('emails.questions', ['phone' => config('salon.phone')]) }}

{!! nl2br(e(__('emails.signature'))) !!}
</x-mail::message>
