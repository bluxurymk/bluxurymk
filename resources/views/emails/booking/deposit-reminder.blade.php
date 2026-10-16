<x-mail::message>
# {{ __('emails.greeting', ['name' => $booking->customer_name]) }}

{{ __('emails.deposit_reminder.intro') }}

@include('emails.booking.partials.details')

{!! __('emails.deposit_reminder.deposit', ['amount' => e($amount), 'deadline' => e($deadline)]) !!}

<x-mail::button :url="$manageUrl">
{{ __('emails.manage') }}
</x-mail::button>

{{ __('emails.questions', ['phone' => config('salon.phone')]) }}

{!! nl2br(e(__('emails.signature'))) !!}
</x-mail::message>
