<x-mail::message>
# {{ __('emails.greeting', ['name' => $booking->customer_name]) }}

{{ __('emails.confirmed.intro') }}

@include('emails.booking.partials.details')

{!! __('emails.add_to_calendar', ['url' => $googleCalendarUrl]) !!}

<x-mail::button :url="$manageUrl">
{{ __('emails.manage') }}
</x-mail::button>

{{ __('emails.manage_hint') }} {{ __('emails.questions', ['phone' => config('salon.phone')]) }}

{!! nl2br(e(__('emails.signature'))) !!}
</x-mail::message>
