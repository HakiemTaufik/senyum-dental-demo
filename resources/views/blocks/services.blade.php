@php
    $services = \App\Models\Service::published()->ordered()
        ->when(filled($data['limit'] ?? null), fn ($q) => $q->limit((int) $data['limit']))
        ->get();
    $hasServicesPage = \App\Models\Page::published()->where('slug', 'services')->exists();
    // Card titles sit one level below the section heading (h2 when the section has none).
    $cardHeading = filled($data['heading'] ?? null) ? 'h3' : 'h2';
@endphp
<section class="section bg-white">
    <div class="container-site">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                @if (filled($data['heading'] ?? null))
                    <h2 class="section-heading">{{ $data['heading'] }}</h2>
                @endif
                @if (filled($data['intro'] ?? null))
                    <p class="section-intro">{{ $data['intro'] }}</p>
                @endif
            </div>
            @if (($data['show_all_link'] ?? false) && $hasServicesPage)
                <a href="{{ route('page', 'services') }}" class="inline-flex items-center gap-1 font-semibold text-brand-700 underline-offset-4 hover:underline">All services <x-site-icon name="arrow-right" class="size-5" /></a>
            @endif
        </div>
        <ul class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                <li class="card relative flex flex-col transition hover:border-brand-300 hover:shadow-md">
                    <span class="inline-flex size-12 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                        <x-site-icon :name="$service->icon ?? 'tooth'" />
                    </span>
                    <{{ $cardHeading }} class="mt-4 text-xl font-semibold">
                        <a href="{{ route('services.show', $service) }}" class="after:absolute after:inset-0 after:rounded-2xl">{{ $service->name }}</a>
                    </{{ $cardHeading }}>
                    <p class="mt-2 flex-1 text-ink-muted">{{ $service->summary }}</p>
                    @if ($service->price_note || $service->duration_minutes)
                        <p class="mt-4 text-sm font-medium text-brand-800">
                            {{ collect([$service->price_note, $service->duration_minutes ? 'about '.$service->duration_minutes.' min' : null])->filter()->implode(' · ') }}
                        </p>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</section>
