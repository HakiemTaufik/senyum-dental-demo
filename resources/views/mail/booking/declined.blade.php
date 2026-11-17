<x-mail::message>
# About your appointment request

Hello {{ $booking->name }}, thank you for contacting us. Unfortunately we are not able to offer this appointment.

@if ($booking->message_to_patient)
{{ $booking->message_to_patient }}
@endif

Your reference was **{{ $booking->reference }}**. You are welcome to call or WhatsApp us to find another option.

If you have severe pain, swelling or bleeding outside our opening hours, please go to the nearest hospital emergency department.

@include('mail.booking._footer')
</x-mail::message>
