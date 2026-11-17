@php
    $image = \App\Support\Html::image($data['image'] ?? null);
    $buttonUrl = \App\Support\Html::safeUrl($data['button_url'] ?? null);
    $imageLeft = ($data['image_position'] ?? 'right') === 'left';
@endphp
<section class="section">
    <div class="container-site grid items-center gap-10 lg:grid-cols-2">
        <div @class(['lg:order-2' => $imageLeft])>
            @if (filled($data['heading'] ?? null))
                <h2 class="section-heading">{{ $data['heading'] }}</h2>
            @endif
            <div class="rich mt-5">{!! \App\Support\Html::rich($data['body'] ?? null) !!}</div>
            @if ($buttonUrl && filled($data['button_label'] ?? null))
                <a href="{{ $buttonUrl }}" class="btn-primary mt-6">{{ $data['button_label'] }}</a>
            @endif
        </div>
        <div @class(['lg:order-1' => $imageLeft])>
            @if ($image)
                <img src="{{ $image }}" alt="{{ $data['image_alt'] ?? '' }}" class="aspect-[4/3] w-full rounded-3xl object-cover shadow-md" loading="lazy" decoding="async">
            @else
                <div aria-hidden="true" class="flex aspect-[4/3] items-center justify-center rounded-3xl bg-gradient-to-br from-brand-100 to-brand-200">
                    <x-site-icon name="smile" class="size-24 text-brand-600" />
                </div>
            @endif
        </div>
    </div>
</section>
