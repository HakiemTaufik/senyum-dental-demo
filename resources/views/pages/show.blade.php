@php
    $blocks = $page->content ?? [];
    $heroImage = ($blocks[0]['type'] ?? null) === 'hero' ? \App\Support\Html::image($blocks[0]['data']['image'] ?? null) : null;
@endphp
<x-layouts.site
    :title="$page->isHome() ? null : ($page->seo_title ?: $page->title)"
    :full-title="$page->isHome() ? $page->seo_title : null"
    :description="$page->seo_description"
    :image="$heroImage">

    @if ($page->isHome() || $page->slug === 'contact')
        @push('jsonld')
            {{ \App\Support\StructuredData::script(\App\Support\StructuredData::dentist()) }}
        @endpush
    @endif

    {{-- Pages that don't start with a hero banner get a simple heading. --}}
    @if (($blocks[0]['type'] ?? null) !== 'hero')
        <div class="border-b border-sand-200 bg-brand-50">
            <div class="container-site py-12 sm:py-16">
                <h1 class="text-4xl font-bold sm:text-5xl">{{ $page->title }}</h1>
            </div>
        </div>
    @endif

    @foreach ($blocks as $index => $block)
        @includeIf('blocks.'.$block['type'], ['data' => $block['data'] ?? [], 'isFirst' => $index === 0])
    @endforeach
</x-layouts.site>
