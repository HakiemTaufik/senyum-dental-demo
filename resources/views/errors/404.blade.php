<x-layouts.site title="Page not found" :noindex="true">
    <div class="container-site max-w-2xl py-20 text-center sm:py-28">
        <p class="text-sm font-semibold uppercase tracking-widest text-brand-600">Error 404</p>
        <h1 class="mt-3 text-4xl font-bold sm:text-5xl">We couldn't find that page</h1>
        <p class="mt-4 text-lg text-ink-muted">It may have moved, or the link may be mistyped.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('home') }}" class="btn-primary">Go to the home page</a>
            <a href="{{ route('book') }}" class="btn-secondary">Request an appointment</a>
        </div>
    </div>
</x-layouts.site>
