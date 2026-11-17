<x-mail::message>
# New appointment request

A new request has arrived from the website.

@include('mail.booking._details')

| | |
|:--|:--|
| Patient | {{ $booking->patient_type->getLabel() }} |
| Received | {{ $booking->created_at->format('j M Y, g:i a') }} |

For privacy, the patient's contact details are only shown in the admin panel.

<x-mail::button :url="\App\Filament\Resources\BookingRequests\BookingRequestResource::getUrl('view', ['record' => $booking])">
Open in admin panel
</x-mail::button>

Please reply to the patient within one working day.
</x-mail::message>
