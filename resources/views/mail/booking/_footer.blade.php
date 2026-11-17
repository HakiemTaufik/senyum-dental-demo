**{{ $clinic->name }}**<br>
{!! nl2br(e($clinic->address)) !!}<br>
@if ($clinic->phone)Phone: {{ $clinic->phone }}<br>@endif
@if ($clinic->whatsappUrl())WhatsApp: [{{ $clinic->whatsapp }}]({{ $clinic->whatsappUrl() }})@endif

<small>You are receiving this email because an appointment request was made on our website with this address. If this wasn't you, please let us know and we will delete the details. Read our [privacy policy]({{ url('/privacy-policy') }}).</small>
