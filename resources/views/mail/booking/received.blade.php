<x-mail::message>
# Thank you, {{ $booking->name }}

We have received your appointment request. **This is not yet a confirmed appointment.** We will contact you, usually within one working day, to confirm a time.

@include('mail.booking._details')

If you need to change or cancel your request, just reply to this email or call us, quoting your reference **{{ $booking->reference }}**.

If you have severe pain, swelling or bleeding, please call us instead of waiting for a reply.

@include('mail.booking._footer')
</x-mail::message>
