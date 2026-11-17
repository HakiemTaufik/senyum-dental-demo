@php($buttonUrl = \App\Support\Html::safeUrl($data['button_url'] ?? null))
<section class="section">
    <div class="container-site">
        <div class="rounded-3xl bg-brand-700 px-6 py-12 text-center text-white sm:px-12">
            <h2 class="text-3xl font-bold text-white sm:text-4xl">{{ $data['heading'] ?? '' }}</h2>
            @if (filled($data['text'] ?? null))
                <p class="mx-auto mt-3 max-w-2xl text-lg text-brand-50">{{ $data['text'] }}</p>
            @endif
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                @if ($buttonUrl && filled($data['button_label'] ?? null))
                    <a href="{{ $buttonUrl }}" class="btn bg-white text-brand-800 hover:bg-brand-50">{{ $data['button_label'] }}</a>
                @endif
                @if (($data['show_whatsapp'] ?? false) && $clinic->whatsappUrl())
                    <a href="{{ $clinic->whatsappUrl('Hi, I would like to ask about an appointment.') }}" class="btn border border-white/70 text-white hover:bg-white/10" rel="noopener" target="_blank">
                        <x-site-icon name="whatsapp" class="size-5" /> WhatsApp us<span class="sr-only"> (opens in a new tab)</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
