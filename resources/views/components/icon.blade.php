@props(['name'])
@php
    $paths = [
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
        'location_on' => '<path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
        'person' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'check_circle' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/>',
        'error' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v6M12 17h.01"/>',
        'swap_vert' => '<path d="m8 3-3 3 3 3M5 6h8M16 21l3-3-3-3M19 18h-8"/>',
        'arrow_forward' => '<path d="M5 12h14M14 7l5 5-5 5"/>',
        'chevron_right' => '<path d="m9 5 7 7-7 7"/>',
        'description' => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>',
        'search_off' => '<circle cx="10" cy="10" r="6"/><path d="m15 15 5 5M4 4l16 16"/>',
        'notifications' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
        'bolt' => '<path d="m13 2-8 12h7l-1 8 8-12h-7z"/>',
        'restaurant' => '<path d="M7 3v8M4 3v5a3 3 0 0 0 6 0V3M7 11v10M16 3v18M16 3c3 2 4 6 0 10"/>',
        'storefront' => '<path d="M4 10v11h16V10M3 10l2-7h14l2 7M3 10a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0M9 21v-6h6v6"/>',
        'celebration' => '<path d="m4 20 5-14 9 9zM13 4l1-2M18 8l3-1M17 3l2-1M8 14l5-5"/>',
        'inventory_2' => '<path d="M4 7h16v14H4zM3 3h18v4H3zM9 11h6"/>',
        'bookmark' => '<path d="M6 3h12v18l-6-4-6 4z"/>',
        'wallet' => '<path d="M3 6h16v14H3zM3 9h16M15 13h6v4h-6z"/>',
        'star' => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9z"/>',
        'messages' => '<path d="M4 5h16v11H8l-4 4z"/><path d="M8 9h8M8 12h5"/>',
    ];
    $content = $paths[$name] ?? '<circle cx="12" cy="12" r="9"/>';
@endphp
<svg {{ $attributes->class('svg-icon') }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $content !!}</svg>

