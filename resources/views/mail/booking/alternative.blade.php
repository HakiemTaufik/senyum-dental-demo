<x-mail::message>
# Another time for your appointment

Hello {{ $booking->name }}, thank you for your request. Unfortunately we can't see you at your preferred time. We can offer:

<x-mail::panel>
**{{ $booking->alternative_at?->format('l, j F Y, g:i a') }}**
</x-mail::panel>

@if ($booking->message_to_patient)
**Message from the clinic:**<br>
{{ $booking->message_to_patient }}
@endif

**Does this time suit you?** Please reply to this email, call us or send a WhatsApp message quoting **{{ $booking->reference }}**. Your appointment is confirmed once we hear back from you.

@if ($clinic->whatsappUrl())
<x-mail::button :url="$clinic->whatsappUrl('Hi, about my booking '.$booking->reference.': ')">
Reply on WhatsApp
</x-mail::button>
@endif

@include('mail.booking._footer')
</x-mail::message>
