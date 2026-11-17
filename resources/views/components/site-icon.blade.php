@props(['name' => 'tooth'])
@php
    // Simple outline icons (24×24). Decorative: hidden from screen readers.
    $paths = [
        'tooth' => '<path d="M7 3c-2.2 0-4 1.9-4 4.4 0 2.2.8 3.4 1.5 4.6.6 1.1.9 2.6 1.1 4.3.3 2.6.9 4.7 2.1 4.7 1.4 0 1.6-2.4 2-4.4.3-1.4.8-2.1 2.3-2.1s2 .7 2.3 2.1c.4 2 .6 4.4 2 4.4 1.2 0 1.8-2.1 2.1-4.7.2-1.7.5-3.2 1.1-4.3.7-1.2 1.5-2.4 1.5-4.6C21 4.9 19.2 3 17 3c-1.9 0-2.9 1-5 1S8.9 3 7 3Z"/>',
        'sparkle' => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8L19 15Z"/>',
        'shield' => '<path d="M12 3l8 3v6c0 4.5-3.4 8.3-8 9-4.6-.7-8-4.5-8-9V6l8-3Z"/><path d="m9 12 2 2 4-4"/>',
        'smile' => '<circle cx="12" cy="12" r="9"/><path d="M8 14c1 1.5 2.4 2.2 4 2.2s3-.7 4-2.2"/><path d="M9 9.5h.01M15 9.5h.01"/>',
        'child' => '<circle cx="12" cy="6" r="3"/><path d="M8 21v-5l-2-3 3-3h6l3 3-2 3v5"/>',
        'braces' => '<path d="M3 9c3 2 15 2 18 0M3 15c3-2 15-2 18 0"/><rect x="5" y="10" width="3" height="4" rx="1"/><rect x="10.5" y="10" width="3" height="4" rx="1"/><rect x="16" y="10" width="3" height="4" rx="1"/>',
        'crown' => '<path d="M3 8l4 4 5-7 5 7 4-4-2 11H5L3 8Z"/>',
        'emergency' => '<rect x="3" y="6" width="18" height="14" rx="2"/><path d="M9 6V4h6v2M12 10v6M9 13h6"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'heart' => '<path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10Z"/>',
        'chat' => '<path d="M4 5h16v11H8l-4 4V5Z"/>',
        'location' => '<path d="M12 21s-7-6.2-7-12a7 7 0 0 1 14 0c0 5.8-7 12-7 12Z"/><circle cx="12" cy="9" r="2.5"/>',
        'accessible' => '<circle cx="12" cy="4.5" r="1.5"/><path d="M6 8h12M12 8v5M9 21l3-8 3 8"/>',
        'phone' => '<path d="M5 3h4l2 5-2.5 1.5a11 11 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 5a2 2 0 0 1 2-2Z"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'whatsapp' => '<path d="M4 20l1.3-4A8 8 0 1 1 8 18.7L4 20Z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1-1.5-2-1-1 .8a4 4 0 0 1-1.8-1.8l.8-1-1-2L9 9.5Z"/>',
        'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
    ];
@endphp
<svg {{ $attributes->merge(['class' => 'size-6 shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $paths[$name] ?? $paths['tooth'] !!}</svg>
