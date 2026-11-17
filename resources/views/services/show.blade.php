<x-layouts.site :title="$service->seo_title ?: $service->name" :description="$service->seo_description ?: $service->summary" :image="\App\Support\Html::image($service->image)">
    @push('jsonld')
        {{ \App\Support\StructuredData::script(\App\Support\StructuredData::breadcrumbs([['Home', route('home')], ['Services', route('page', 'services')], [$service->name, route('services.show', $service)]])) }}
    @endpush
    <div class="border-b border-sand-200 bg-brand-50">
        <div class="container-site py-12 sm:py-16">
            <nav aria-label="Breadcrumb" class="text-sm text-ink-muted">
                <ol class="flex flex-wrap gap-2">
                    <li><a href="{{ route('home') }}" class="underline-offset-2 hover:underline">Home</a> <span aria-hidden="true">/</span></li>
                    <li><a href="{{ route('page', 'services') }}" class="underline-offset-2 hover:underline">Services</a> <span aria-hidden="true">/</span></li>
                    <li aria-current="page">{{ $service->name }}</li>
                </ol>
            </nav>
            <div class="mt-4 flex items-start gap-4">
                <span class="hidden size-14 shrink-0 items-center justify-center rounded-2xl bg-white text-brand-700 shadow-sm sm:inline-flex"><x-site-icon :name="$service->icon ?? 'tooth'" class="size-8" /></span>
                <div>
                    <h1 class="text-4xl font-bold sm:text-5xl">{{ $service->name }}</h1>
                    <p class="section-intro">{{ $service->summary }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container-site grid gap-10 py-14 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if ($image = \App\Support\Html::image($service->image))
                <img src="{{ $image }}" alt="{{ $service->image_alt ?? '' }}" class="mb-8 aspect-[16/9] w-full rounded-3xl object-cover" fetchpriority="high" decoding="async">
            @endif
            <div class="rich">{!! \App\Support\Html::rich($service->description) !!}</div>
        </div>
        <aside class="lg:sticky lg:top-28 lg:self-start" aria-label="Appointment">
            <div class="card">
                <h2 class="text-lg font-semibold">At a glance</h2>
                <dl class="mt-4 space-y-3">
                    @if ($service->duration_minutes)
                        <div class="flex items-center gap-3"><x-site-icon name="clock" class="text-brand-600" /><dt class="sr-only">Duration</dt><dd>About {{ $service->duration_minutes }} minutes</dd></div>
                    @endif
                    @if ($service->price_note)
                        <div class="flex items-center gap-3"><x-site-icon name="info" class="text-brand-600" /><dt class="sr-only">Fee</dt><dd>{{ $service->price_note }}</dd></div>
                    @endif
                </dl>
                <p class="mt-4 text-sm text-ink-muted">Every patient is different. Your dentist will explain your options and costs before any treatment.</p>
                @if ($service->is_bookable)
                    <a href="{{ route('book', ['service' => $service->slug]) }}" class="btn-primary mt-6 w-full">Request an appointment</a>
                @endif
                @if ($clinic->whatsappUrl())
                    <a href="{{ $clinic->whatsappUrl('Hi, I have a question about '.$service->name.'.') }}" class="btn-secondary mt-3 w-full" rel="noopener" target="_blank">Ask on WhatsApp<span class="sr-only"> (opens in a new tab)</span></a>
                @endif
            </div>
        </aside>
    </div>

    @if ($otherServices->isNotEmpty())
        <section class="border-t border-sand-200 bg-white py-14">
            <div class="container-site">
                <h2 class="text-2xl font-bold">Other services</h2>
                <ul class="mt-6 grid gap-4 sm:grid-cols-3">
                    @foreach ($otherServices as $other)
                        <li><a href="{{ route('services.show', $other) }}" class="card flex items-center gap-3 font-semibold text-brand-800 hover:border-brand-300"><x-site-icon :name="$other->icon ?? 'tooth'" class="text-brand-600" /> {{ $other->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
</x-layouts.site>
