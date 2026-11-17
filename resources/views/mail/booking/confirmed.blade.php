<x-mail::message>
# Your appointment is confirmed

Hello {{ $booking->name }}, we look forward to seeing you.

@include('mail.booking._details')

@if ($booking->message_to_patient)
**Message from the clinic:**<br>
{{ $booking->message_to_patient }}
@endif

**Before your visit**
- Please arrive 10 minutes early and bring your identity card (or passport).
- Bring a list of any medicines you take.
- A calendar file is attached so you can add the appointment to your phone.

If you can't make it, please call or reply to this email as soon as possible so we can offer the time to someone else.

@if ($clinic->maps_url)
<x-mail::button :url="$clinic->maps_url">
Get directions
</x-mail::button>
@endif

@include('mail.booking._footer')
</x-mail::message>
