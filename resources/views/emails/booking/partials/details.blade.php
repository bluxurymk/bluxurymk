<x-mail::panel>
**{{ __('emails.labels.service') }}:** {{ $booking->service->name }}<br>
@if ($booking->stylist)
**{{ __('emails.labels.stylist') }}:** {{ $booking->stylist->name }}<br>
@endif
**{{ __('emails.labels.when') }}:** {{ $when }}<br>
**{{ __('emails.labels.address') }}:** {{ config('salon.address') }}, {{ config('salon.city') }}
</x-mail::panel>
