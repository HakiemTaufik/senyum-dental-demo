@php
    $faqs = \App\Models\Faq::published()->ordered()
        ->when(filled($data['category'] ?? null), fn ($q) => $q->where('category', $data['category']))
        ->when(filled($data['limit'] ?? null), fn ($q) => $q->limit((int) $data['limit']))
        ->get();
    $hasFaqPage = \App\Models\Page::published()->where('slug', 'faq')->exists();
@endphp
@if (blank($data['limit'] ?? null) && blank($data['category'] ?? null) && $faqs->isNotEmpty())
    @push('jsonld')
        {{ \App\Support\StructuredData::script(\App\Support\StructuredData::faqPage($faqs)) }}
    @endpush
@endif
<section class="section">
    <div class="container-site max-w-3xl">
        @if (filled($data['heading'] ?? null))
            <h2 class="section-heading">{{ $data['heading'] }}</h2>
        @endif
        <div class="mt-8 divide-y divide-sand-200 rounded-2xl border border-sand-200 bg-white">
            @foreach ($faqs as $faq)
                <details class="group p-5 [&_summary::-webkit-details-marker]:hidden">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 rounded-md text-lg font-semibold text-brand-900">
                        {{ $faq->question }}
                        <span aria-hidden="true" class="text-2xl leading-none text-brand-600 transition group-open:rotate-45">+</span>
                    </summary>
                    <div class="rich prose-base mt-3">{!! \App\Support\Html::rich($faq->answer) !!}</div>
                </details>
            @endforeach
        </div>
        @if (($data['show_all_link'] ?? false) && $hasFaqPage)
            <a href="{{ route('page', 'faq') }}" class="mt-6 inline-flex items-center gap-1 font-semibold text-brand-700 underline-offset-4 hover:underline">All questions <x-site-icon name="arrow-right" class="size-5" /></a>
        @endif
    </div>
</section>
