<x-mail::table>
| | |
|:--|:--|
| Reference | **{{ $booking->reference }}** |
| Service | {{ $booking->serviceName() }} |
@if ($booking->appointment_at && $booking->status === \App\Enums\BookingStatus::Confirmed)
| Date & time | **{{ $booking->appointment_at->format('l, j F Y, g:i a') }}** |
@else
| Preferred date | {{ $booking->preferred_date->format('l, j F Y') }} |
| Preferred time | {{ $booking->preferred_time->getLabel() }} |
@endif
@if ($booking->dentist)
| Dentist | {{ $booking->dentist->name }} |
@endif
</x-mail::table>
