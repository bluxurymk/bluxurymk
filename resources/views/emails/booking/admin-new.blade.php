<x-mail::message>
# {{ $booking->customer_name }}

{{ __('emails.admin_new.intro', ['amount' => $amount, 'deadline' => $deadline]) }}

<x-mail::panel>
**{{ __('emails.labels.service') }}:** {{ $booking->service->name }}<br>
@if ($booking->stylist)
**{{ __('emails.labels.stylist') }}:** {{ $booking->stylist->name }}<br>
@endif
**{{ __('emails.labels.when') }}:** {{ $when }}<br>
**{{ __('emails.labels.phone') }}:** {{ $booking->customer_phone }}<br>
@if ($booking->customer_email)
**{{ __('emails.labels.email') }}:** {{ $booking->customer_email }}<br>
@endif
@if ($booking->note)
**{{ __('emails.labels.note') }}:** {{ $booking->note }}
@endif
</x-mail::panel>

<x-mail::button :url="\App\Filament\Resources\Bookings\BookingResource::getUrl('edit', ['record' => $booking], panel: 'admin')">
{{ __('emails.admin_new.open') }}
</x-mail::button>
</x-mail::message>
