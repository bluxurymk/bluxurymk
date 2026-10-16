<x-mail::message>
# {{ __('emails.greeting', ['name' => $booking->customer_name]) }}

{{ __('emails.reminder.intro') }}

@include('emails.booking.partials.details')

{{ __('emails.reminder.cant_come') }}

<x-mail::button :url="$manageUrl">
{{ __('emails.manage') }}
</x-mail::button>

{{ __('emails.questions', ['phone' => config('salon.phone')]) }}

{!! nl2br(e(__('emails.signature'))) !!}
</x-mail::message>
