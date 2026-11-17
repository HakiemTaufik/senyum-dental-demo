<x-mail::message>
# Your appointment has been cancelled

Hello {{ $booking->name }}, your appointment (reference **{{ $booking->reference }}**) has been cancelled.

@if ($booking->message_to_patient)
{{ $booking->message_to_patient }}
@endif

If this is unexpected, or you would like a new appointment, please call or WhatsApp us.

@include('mail.booking._footer')
</x-mail::message>
