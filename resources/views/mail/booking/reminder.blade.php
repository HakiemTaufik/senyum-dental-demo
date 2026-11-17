<x-mail::message>
# See you tomorrow

Hello {{ $booking->name }}, this is a friendly reminder of your appointment.

@include('mail.booking._details')

Please arrive 10 minutes early and bring your identity card. If you can't make it, please call us or reply to this email as soon as possible.

@if ($clinic->maps_url)
<x-mail::button :url="$clinic->maps_url">
Get directions
</x-mail::button>
@endif

@include('mail.booking._footer')
</x-mail::message>
