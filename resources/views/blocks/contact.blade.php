@php($cardHeading = filled($data['heading'] ?? null) ? 'h3' : 'h2') {{-- one level below the section heading --}}
<section class="section">
    <div class="container-site">
        @if (filled($data['heading'] ?? null))
            <h2 class="section-heading">{{ $data['heading'] }}</h2>
        @endif
        @if (filled($data['intro'] ?? null))
            <p class="section-intro">{{ $data['intro'] }}</p>
        @endif
        <div class="mt-10 grid gap-6 lg:grid-cols-3">
            <div class="card">
                <{{ $cardHeading }} class="flex items-center gap-2 text-lg font-semibold"><x-site-icon name="location" class="text-brand-600" /> Address</{{ $cardHeading }}>
                @if ($clinic->address)
                    <address class="mt-3 not-italic text-ink-muted">{!! nl2br(e($clinic->address)) !!}</address>
                @endif
                @if ($clinic->maps_url)
                    <a href="{{ $clinic->maps_url }}" class="btn-secondary mt-5" rel="noopener" target="_blank">Get directions<span class="sr-only"> (Google Maps, opens in a new tab)</span></a>
                @endif
            </div>
            <div class="card">
                <{{ $cardHeading }} class="flex items-center gap-2 text-lg font-semibold"><x-site-icon name="phone" class="text-brand-600" /> Call or message</{{ $cardHeading }}>
                <ul class="mt-3 space-y-3">
                    @if ($clinic->phoneHref())
                        <li><a href="{{ $clinic->phoneHref() }}" class="font-semibold text-brand-700 underline-offset-4 hover:underline">{{ $clinic->phone }}</a></li>
                    @endif
                    @if ($clinic->email)
                        <li><a href="mailto:{{ $clinic->email }}" class="font-semibold text-brand-700 underline-offset-4 hover:underline">{{ $clinic->email }}</a></li>
                    @endif
                    @if ($clinic->whatsappUrl())
                        <li><a href="{{ $clinic->whatsappUrl('Hi, I have a question.') }}" class="btn-whatsapp mt-2" rel="noopener" target="_blank"><x-site-icon name="whatsapp" class="size-5" /> WhatsApp us<span class="sr-only"> (opens in a new tab)</span></a></li>
                    @endif
                </ul>
            </div>
            <div class="card">
                <{{ $cardHeading }} class="flex items-center gap-2 text-lg font-semibold"><x-site-icon name="clock" class="text-brand-600" /> Opening hours</{{ $cardHeading }}>
                <dl class="mt-3 space-y-2">
                    @foreach ($clinic->opening_hours as $row)
                        <div class="flex justify-between gap-4 border-b border-sand-100 pb-2 last:border-0"><dt class="text-ink-muted">{{ $row['days'] }}</dt><dd class="text-right font-medium">{{ $row['hours'] }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </div>
    </div>
</section>
