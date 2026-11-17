@php
    $dentists = \App\Models\Dentist::published()->ordered()->get();
    // Card titles sit one level below the section heading (h2 when the section has none).
    $cardHeading = filled($data['heading'] ?? null) ? 'h3' : 'h2';
@endphp
<section class="section">
    <div class="container-site">
        @if (filled($data['heading'] ?? null))
            <h2 class="section-heading">{{ $data['heading'] }}</h2>
        @endif
        @if (filled($data['intro'] ?? null))
            <p class="section-intro">{{ $data['intro'] }}</p>
        @endif
        <ul class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($dentists as $dentist)
                <li class="card">
                    <div class="flex items-center gap-4">
                        @if ($photo = \App\Support\Html::image($dentist->photo))
                            <img src="{{ $photo }}" alt="" class="size-20 rounded-full object-cover" loading="lazy" decoding="async" width="80" height="80">
                        @else
                            <span aria-hidden="true" class="inline-flex size-20 shrink-0 items-center justify-center rounded-full bg-brand-100 text-2xl font-bold text-brand-800">{{ $dentist->initials() }}</span>
                        @endif
                        <div>
                            <{{ $cardHeading }} class="text-xl font-semibold">{{ $dentist->name }}</{{ $cardHeading }}>
                            @if ($dentist->position)<p class="text-ink-muted">{{ $dentist->position }}</p>@endif
                        </div>
                    </div>
                    @if ($dentist->qualifications)
                        <p class="mt-4 text-sm font-medium text-brand-800">{{ $dentist->qualifications }}</p>
                    @endif
                    @if ($dentist->bio)
                        <p class="mt-3 text-ink-muted">{{ $dentist->bio }}</p>
                    @endif
                    <dl class="mt-4 space-y-1 text-sm text-ink-muted">
                        @if ($dentist->languages)
                            <div><dt class="inline font-medium text-ink">Languages:</dt> <dd class="inline">{{ $dentist->languages }}</dd></div>
                        @endif
                        @if ($dentist->mdc_registration)
                            <div><dt class="inline font-medium text-ink">MDC reg. no.:</dt> <dd class="inline">{{ $dentist->mdc_registration }}</dd></div>
                        @endif
                    </dl>
                </li>
            @endforeach
        </ul>
    </div>
</section>
