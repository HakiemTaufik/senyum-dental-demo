{{-- Simple text logo: a smile mark + "Senyum" wordmark. Replace with the real logo file later. --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
    <svg class="size-9" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
        <circle cx="20" cy="20" r="19" fill="var(--color-brand-600)"/>
        <path d="M11 20.5c2.2 4.6 5.4 7 9 7s6.8-2.4 9-7" fill="none" stroke="#fff" stroke-width="3.2" stroke-linecap="round"/>
        <circle cx="29.5" cy="12.5" r="2.4" fill="var(--color-accent)"/>
    </svg>
    <span class="leading-none">
        <span class="block text-xl font-bold tracking-tight text-brand-800">Senyum</span>
        <span class="block text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-ink-muted">Dental Clinic</span>
    </span>
</span>
