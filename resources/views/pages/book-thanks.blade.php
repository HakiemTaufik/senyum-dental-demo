<x-layouts.site title="Request received" :noindex="true">
    <div class="container-site max-w-2xl py-16 sm:py-24">
        <div class="card text-center sm:p-10">
            <span class="mx-auto inline-flex size-16 items-center justify-center rounded-full bg-brand-100 text-brand-700">
                <x-site-icon name="calendar" class="size-8" />
            </span>
            <h1 class="mt-6 text-3xl font-bold sm:text-4xl">Thank you – we have your request</h1>
            <p class="mt-4 text-lg text-ink-muted">Your reference is</p>
            <p class="mt-1 text-3xl font-bold tracking-wider text-brand-800">{{ $reference }}</p>
            <p class="mt-6 text-ink-muted">
                <strong class="text-ink">This is not yet a confirmed appointment.</strong>
                We will contact you, usually within one working day, to confirm a time.
                @if ($email) A copy has been sent to <strong class="text-ink">{{ $email }}</strong> (check your spam folder if you don't see it). @endif
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('home') }}" class="btn-secondary">Back to home</a>
                @if ($clinic->whatsappUrl())
                    <a href="{{ $clinic->whatsappUrl('Hi, about my booking '.$reference.': ') }}" class="btn-whatsapp" rel="noopener" target="_blank"><x-site-icon name="whatsapp" class="size-5" /> Questions? WhatsApp us<span class="sr-only"> (opens in a new tab)</span></a>
                @endif
            </div>
        </div>
    </div>
</x-layouts.site>
