<section class="section" @if (filled($data['heading'] ?? null)) id="{{ \Illuminate\Support\Str::slug($data['heading']) }}" @endif>
    <div class="container-site max-w-3xl">
        @if (filled($data['heading'] ?? null))
            <h2 class="section-heading mb-6">{{ $data['heading'] }}</h2>
        @endif
        <div class="rich">{!! \App\Support\Html::rich($data['body'] ?? null) !!}</div>
    </div>
</section>
