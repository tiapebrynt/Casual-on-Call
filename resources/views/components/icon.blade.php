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
        'eye' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'eye_off' => '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>',
        'edit' => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
        'delete' => '<path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'close' => '<path d="M18 6 6 18M6 6l12 12"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'calendar' => '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>',
        'briefcase' => '<rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        'building' => '<rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M8 10h.01M16 10h.01M8 14h.01M16 14h.01M8 18h.01M16 18h.01"/>',
        'cash' => '<rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/>',
        'sparkles' => '<path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
        'quote' => '<path d="M3 21c3 0 7-1 7-8V5c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v6c0 1.1.9 2 2 2 0 4-1 6-1 8zm14 0c3 0 7-1 7-8V5c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v6c0 1.1.9 2 2 2 0 4-1 6-1 8z"/>',
    ];
    $content = $paths[$name] ?? '<circle cx="12" cy="12" r="9"/>';
@endphp
<svg {{ $attributes->class('svg-icon') }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $content !!}</svg>

