@php
    $image = \App\Support\Html::image($data['image'] ?? null);
    $buttonUrl = \App\Support\Html::safeUrl($data['button_url'] ?? null);
    $headingTag = $isFirst ? 'h1' : 'h2';
@endphp
<section class="relative overflow-hidden bg-gradient-to-b from-brand-50 to-sand-50">
    <div aria-hidden="true" class="pointer-events-none absolute -right-24 -top-24 size-96 rounded-full bg-brand-100/70 blur-3xl"></div>
    <div class="container-site relative grid items-center gap-10 py-14 sm:py-20 lg:grid-cols-2">
        <div>
            <{{ $headingTag }} class="text-4xl font-bold leading-tight sm:text-5xl lg:text-6xl">{{ $data['heading'] ?? '' }}</{{ $headingTag }}>
            @if (filled($data['text'] ?? null))
                <p class="mt-5 max-w-xl text-lg text-ink-muted sm:text-xl">{{ $data['text'] }}</p>
            @endif
            <div class="mt-8 flex flex-wrap gap-3">
                @if ($buttonUrl && filled($data['button_label'] ?? null))
                    <a href="{{ $buttonUrl }}" class="btn-primary">{{ $data['button_label'] }} <x-site-icon name="arrow-right" class="size-5" /></a>
                @endif
                @if (($data['show_whatsapp'] ?? false) && $clinic->whatsappUrl())
                    <a href="{{ $clinic->whatsappUrl('Hi, I would like to ask about an appointment.') }}" class="btn-whatsapp" rel="noopener" target="_blank">
                        <x-site-icon name="whatsapp" class="size-5" /> WhatsApp us<span class="sr-only"> (opens in a new tab)</span>
                    </a>
                @endif
            </div>
            @if (! empty($clinic->opening_hours))
                <p class="mt-6 flex items-center gap-2 text-sm text-ink-muted">
                    <x-site-icon name="clock" class="size-5 text-brand-600" />
                    {{ $clinic->opening_hours[0]['days'] }}: {{ $clinic->opening_hours[0]['hours'] }}
                </p>
            @endif
        </div>
        <div class="relative">
            @if ($image)
                <img src="{{ $image }}" alt="{{ $data['image_alt'] ?? '' }}" class="aspect-[4/3] w-full rounded-3xl object-cover shadow-lg" fetchpriority="high" decoding="async">
            @else
                {{-- Decorative illustration used until the clinic uploads a photo. --}}
                <div aria-hidden="true" class="relative mx-auto aspect-square w-full max-w-sm lg:max-w-md">
                    <div class="absolute inset-6 rounded-full bg-brand-600"></div>
                    <div class="absolute inset-16 rounded-full bg-brand-500/60"></div>
                    <svg viewBox="0 0 200 200" class="absolute inset-0 m-auto w-3/5 text-white">
                        <path d="M58 38c-18 0-32 15-32 35 0 18 6 27 12 37 5 9 7 21 9 35 3 21 7 38 17 38 11 0 13-20 16-36 3-11 6-17 19-17s16 6 19 17c3 16 5 36 16 36 10 0 14-17 17-38 2-14 4-26 9-35 6-10 12-19 12-37 0-20-14-35-32-35-15 0-23 8-41 8s-26-8-41-8Z" fill="currentColor"/>
                    </svg>
                    <div class="absolute bottom-8 left-0 rounded-2xl bg-white px-4 py-3 shadow-lg">
                        <p class="text-sm font-semibold text-brand-800">Gentle, unhurried care</p>
                    </div>
                    <div class="absolute right-2 top-10 size-10 rounded-full bg-accent"></div>
                </div>
            @endif
        </div>
    </div>
</section>
