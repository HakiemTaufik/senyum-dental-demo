<section class="section">
    <div class="container-site">
        @if (filled($data['heading'] ?? null))
            <h2 class="section-heading">{{ $data['heading'] }}</h2>
        @endif
        @if (filled($data['intro'] ?? null))
            <p class="section-intro">{{ $data['intro'] }}</p>
        @endif
        <ul class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($data['items'] ?? [] as $item)
                <li class="card">
                    <span class="inline-flex size-12 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                        <x-site-icon :name="$item['icon'] ?? 'heart'" />
                    </span>
                    <h3 class="mt-4 text-xl font-semibold">{{ $item['title'] ?? '' }}</h3>
                    @if (filled($item['text'] ?? null))
                        <p class="mt-2 text-ink-muted">{{ $item['text'] }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</section>
