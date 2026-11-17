@props(['title' => null, 'fullTitle' => null, 'description' => null, 'image' => null, 'noindex' => false])
@php
    $pageTitle = $fullTitle ?: ($title ? $title.' | '.$clinic->name : $clinic->name.($clinic->tagline ? ' – '.$clinic->tagline : ''));
    $metaDescription = \Illuminate\Support\Str::limit(strip_tags($description ?? $clinic->tagline ?? ''), 160, '…');
    $currentPath = '/'.ltrim(request()->path(), '/');
    $canonical = url()->current();
    $shareImage = $image ?: asset(config('seo.default_image'));
    $robots = (! config('seo.indexable') || $noindex) ? 'noindex, nofollow' : 'index, follow, max-image-preview:large';
@endphp
<!DOCTYPE html>
<html lang="en-MY" class="scroll-pt-24">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonical }}">

    {{-- How the page looks when shared on WhatsApp, Facebook, etc. --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $clinic->name }}">
    <meta property="og:title" content="{{ $title ?: $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $shareImage }}">
    <meta property="og:locale" content="en_MY">
    <meta name="twitter:card" content="summary_large_image">

    <meta name="theme-color" content="#1f7f74">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    @if ($clinic->analyticsEnabled())
        {{-- Google Analytics is OFF by default; it only starts after the visitor accepts cookies (resources/js/cookies.js). --}}
        <meta name="analytics-id" content="{{ $clinic->analytics_id }}">
        <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('consent', 'default', {analytics_storage: 'denied', ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied'});
        </script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    @stack('jsonld')
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main" class="sr-only z-50 rounded-md bg-white px-4 py-2 font-semibold text-brand-800 shadow focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip to main content</a>

    @if ($clinic->show_demo_notice)
        <div class="bg-brand-900 text-sm text-white" role="note">
            <div class="container-site flex items-start gap-2 py-2">
                <x-site-icon name="info" class="mt-0.5 size-4" />
                <p><strong>Demo website.</strong> Senyum Dental Clinic is a practice project, not a real clinic. Please don't send real personal or health information.</p>
            </div>
        </div>
    @endif

    <header class="sticky top-0 z-40 border-b border-sand-200 bg-white/95 backdrop-blur">
        <div class="container-site flex h-18 items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="rounded-md" aria-label="{{ $clinic->name }} – home">
                <x-logo />
            </a>

            <nav aria-label="Main" class="hidden lg:block">
                <ul class="flex items-center gap-1">
                    @foreach ($menuPages as $item)
                        @php($active = $currentPath === '/'.$item->slug)
                        <li>
                            <a href="{{ route('page', $item->slug) }}"
                               @if ($active) aria-current="page" @endif
                               class="rounded-full px-3 py-2 font-medium text-ink-muted hover:bg-brand-50 hover:text-brand-800 aria-[current=page]:bg-brand-50 aria-[current=page]:text-brand-800">{{ $item->title }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="flex items-center gap-2">
                <a href="{{ route('book') }}" class="btn-primary hidden sm:inline-flex">Request appointment</a>
                <button type="button" class="btn-secondary px-3 lg:hidden" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu">
                    <x-site-icon name="menu" />
                    <span>Menu</span>
                </button>
            </div>
        </div>

        <nav id="mobile-menu" aria-label="Main" class="border-t border-sand-200 bg-white lg:hidden" hidden>
            <ul class="container-site flex flex-col py-3">
                @foreach ($menuPages as $item)
                    <li>
                        <a href="{{ route('page', $item->slug) }}" @if ($currentPath === '/'.$item->slug) aria-current="page" @endif
                           class="block rounded-lg px-3 py-3 text-lg font-medium text-ink hover:bg-brand-50 aria-[current=page]:text-brand-700">{{ $item->title }}</a>
                    </li>
                @endforeach
                <li class="mt-2"><a href="{{ route('book') }}" class="btn-primary w-full">Request appointment</a></li>
            </ul>
        </nav>
    </header>

    <main id="main" tabindex="-1" class="flex-1 focus:outline-none">
        {{ $slot }}
    </main>

    <footer class="mt-auto bg-brand-900 text-brand-100">
        <div class="container-site grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-1">
                <p class="text-xl font-bold text-white">{{ $clinic->name }}</p>
                @if ($clinic->tagline)<p class="mt-2">{{ $clinic->tagline }}</p>@endif
            </div>

            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wider text-white">Visit us</h2>
                @if ($clinic->address)
                    <address class="mt-3 not-italic">{!! nl2br(e($clinic->address)) !!}</address>
                @endif
                @if ($clinic->maps_url)
                    <a href="{{ $clinic->maps_url }}" class="mt-2 inline-block underline underline-offset-2 hover:text-white" rel="noopener" target="_blank">Open in Google Maps<span class="sr-only"> (opens in a new tab)</span></a>
                @endif
            </div>

            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wider text-white">Opening hours</h2>
                <dl class="mt-3 space-y-1">
                    @foreach ($clinic->opening_hours as $row)
                        <div class="flex justify-between gap-4"><dt>{{ $row['days'] }}</dt><dd class="text-right text-white">{{ $row['hours'] }}</dd></div>
                    @endforeach
                </dl>
            </div>

            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wider text-white">Contact</h2>
                <ul class="mt-3 space-y-2">
                    @if ($clinic->phoneHref())
                        <li><a class="hover:text-white" href="{{ $clinic->phoneHref() }}">{{ $clinic->phone }}</a></li>
                    @endif
                    @if ($clinic->whatsappUrl())
                        <li><a class="hover:text-white" href="{{ $clinic->whatsappUrl() }}" rel="noopener" target="_blank">WhatsApp<span class="sr-only"> (opens in a new tab)</span></a></li>
                    @endif
                    @if ($clinic->email)
                        <li><a class="hover:text-white" href="mailto:{{ $clinic->email }}">{{ $clinic->email }}</a></li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10">
            <div class="container-site flex flex-col gap-3 py-6 text-sm sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ now()->year }} {{ $clinic->name }}. @if ($clinic->registration_note)<span class="block sm:inline">{{ $clinic->registration_note }}</span>@endif</p>
                @if ($footerPages->isNotEmpty())
                    <nav aria-label="Legal and other links">
                        <ul class="flex flex-wrap gap-x-5 gap-y-2">
                            @foreach ($footerPages as $item)
                                <li><a class="underline underline-offset-2 hover:text-white" href="{{ route('page', $item->slug) }}">{{ $item->title }}</a></li>
                            @endforeach
                            @if ($clinic->analyticsEnabled())
                                <li><button type="button" data-cc="show-preferencesModal" class="underline underline-offset-2 hover:text-white">Cookie settings</button></li>
                            @endif
                        </ul>
                    </nav>
                @endif
            </div>
        </div>
    </footer>
</body>
</html>
