@props(['name' => 'dot'])

@php
    $paths = [
        'cross' => '<rect x="4" y="4" width="16" height="16" rx="3"/><path stroke-linecap="round" d="M12 8v8M8 12h8"/>',
        'pulse' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12h3.2l1.6-4.2L11 17l2.2-5H21"/><path stroke-linecap="round" stroke-linejoin="round" d="M4.8 19.2C7.2 21 9.6 21.4 12 21.4c5.2 0 9-3.6 9-8.2 0-2.2-.9-4-2.4-5.3"/>',
        'clipboard-plus' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5h6A1.5 1.5 0 0116.5 6v13a1.5 1.5 0 01-1.5 1.5H9A1.5 1.5 0 017.5 19V6A1.5 1.5 0 019 4.5z"/><path stroke-linecap="round" d="M9 4.5V4a1.5 1.5 0 011.5-1.5h3A1.5 1.5 0 0115 4v.5M12 11v5M9.5 13.5h5"/>',
        'clipboard-check' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5h6A1.5 1.5 0 0116.5 6v13a1.5 1.5 0 01-1.5 1.5H9A1.5 1.5 0 017.5 19V6A1.5 1.5 0 019 4.5z"/><path stroke-linecap="round" d="M9 4.5V4a1.5 1.5 0 011.5-1.5h3A1.5 1.5 0 0115 4v.5M9.5 13.2l2 2 3.4-3.6"/>',
        'hospital' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 21V8l8-4 8 4v13M9 21v-6h6v6M12 9v4M10 11h4"/>',
        'flask' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6M10 3v5.2L6.4 16A3 3 0 009 20.5h6A3 3 0 0017.6 16L14 8.2V3M8.5 14h7"/>',
        'syringe' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.5 3.5 20.5 9.5M16.2 1.8l6 6M4 21l2.2-2.2 4.6-1.2 6.2-6.2-3.2-3.2-6.2 6.2L6.4 19 4 21zM11 10.5l2.5 2.5"/>',
        'ambulance' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 14V8.5A1.5 1.5 0 014.5 7H13v7M13 14h3.2L19 16.6V19h-2"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 19a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zM7.5 9.2H11M9.2 7.5v3.4"/>',
        'pill' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.6 15.4 15.4 8.6a3.4 3.4 0 114.8 4.8l-6.8 6.8a3.4 3.4 0 11-4.8-4.8zM10.4 13.6l3.2-3.2"/>',
        'stethoscope' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 3.5v5a4 4 0 008 0v-5M8 3.5H6.2M16 3.5h1.8M12 12.5v1.8A5 5 0 0017 19.2h.6a2.6 2.6 0 100-5.2"/>',
        'heart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-7-4.4-7-10a4 4 0 017-2 4 4 0 017 2c0 5.6-7 10-7 10z"/>',
        'bed' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 18V9.2A1.7 1.7 0 015.7 7.5H11V14h9v4M4 18h16M7.6 7.5V5.4A1 1 0 018.6 4.4h2.2a1 1 0 011 1v2.1"/>',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a3 3 0 00-3-3H8a3 3 0 00-3 3v1m13-10a3 3 0 11-6 0 3 3 0 016 0zm6 10v-1a3 3 0 00-2-2.83"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 8a3 3 0 010 4"/>',
        'clipboard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5h6A1.5 1.5 0 0116.5 6v13a1.5 1.5 0 01-1.5 1.5H9A1.5 1.5 0 017.5 19V6A1.5 1.5 0 019 4.5z"/><path stroke-linecap="round" d="M9 4.5V4a1.5 1.5 0 011.5-1.5h3A1.5 1.5 0 0115 4v.5M9.5 11h5M9.5 14.5h3.5"/>',
        'receipt' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 4h10a1 1 0 011 1v15l-2-1.4L14 20l-2-1.4L10 20l-2-1.4L6 20V5a1 1 0 011-1zm3 5h4M10 13h4"/>',
        'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 4v3m10-3v3M5 9h14M6 20h12a2 2 0 002-2V8a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2zM12 13v4M10 15h4"/>',
        'clock' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'megaphone' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 11v2a3 3 0 003 3h1l1 3h2l-1-3h1l5 2V6l-5 2H8a3 3 0 00-3 3z"/>',
        'grid' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 5h5v5H5V5zm9 0h5v5h-5V5zM5 14h5v5H5v-5zm9 0h5v5h-5v-5z"/>',
        'globe' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zm0 0c2.5-2.2 4-5.4 4-9s-1.5-6.8-4-9c-2.5 2.2-4 5.4-4 9s1.5 6.8 4 9zM3.5 12h17"/>',
        'settings' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 15.5a3.5 3.5 0 110-7 3.5 3.5 0 010 7zM19.4 13a7.8 7.8 0 00.1-2l2-1.2-2-3.4-2.3.6a8 8 0 00-1.7-1L15 3h-4l-.5 2.9a8 8 0 00-1.7 1L6.5 6.4l-2 3.4L6.4 11a7.8 7.8 0 000 2l-1.9 1.2 2 3.4 2.3-.6a8 8 0 001.7 1L11 21h4l.5-2.9a8 8 0 001.7-1l2.3.6 2-3.4L19.4 13z"/>',
        'help' => '<circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 8v8M8 12h8"/>',
        'search' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>',
        'logout' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12H4m11 0-3-3m3 3-3 3m4-10h2a2 2 0 012 2v12a2 2 0 01-2 2h-2"/>',
        'bell' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 11-6 0"/>',
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 10.5 12 4l8 6.5V20a1 1 0 01-1 1h-5v-6H10v6H5a1 1 0 01-1-1v-9.5z"/>',
        'badge' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 4h8l1 3H7l1-3zm-1 3h10v11a2 2 0 01-2 2H9a2 2 0 01-2-2V7zm3 4h4M10 14h4"/>',
        'spark' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3l1.2 4.2L17.5 8.5 13.2 9.8 12 14l-1.2-4.2L6.5 8.5l4.3-1.3L12 3zm6 8 .8 2.4L21 14.5l-2.2.7L18 17.5l-.8-2.3L15 14.5l2.2-.7L18 11z"/>',
        'shield' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 3v6c0 5-3.4 8.4-8 9-4.6-.6-8-4-8-9V6l8-3z"/>',
        'building' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 20V8l8-4 8 4v12M9 20v-6h6v6M9 10h.01M15 10h.01M9 14h.01M15 14h.01"/>',
        'thermometer' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10 13.4V5.8a2 2 0 114 0v7.6a3.5 3.5 0 11-4 0zM12 6.8v6.4"/>',
        'first-aid' => '<rect x="3.5" y="8" width="17" height="12" rx="2"/><path stroke-linecap="round" d="M10 4.5h4L16 8H8l2-3.5zM12 11.5v5M9.5 14h5"/>',
        'clipboard-clock' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5h6A1.5 1.5 0 0116.5 6v4.2M9 4.5V4a1.5 1.5 0 011.5-1.5h3A1.5 1.5 0 0115 4v.5M7.5 10.5V6A1.5 1.5 0 019 4.5"/><circle cx="15.2" cy="16" r="5"/><path stroke-linecap="round" d="M15.2 14v2.1l1.5 1"/>',
        'nurse' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 9.5 12 4.8 16 9.5H8zM12 6.4v2.1M10.8 7.5h2.4M8 14a4 4 0 018 0v6H8v-6z"/>',
        'dot' => '<circle cx="12" cy="12" r="3"/>',
    ];

    $paths['activity'] = $paths['pulse'];
    $paths['heart-pulse'] = $paths['pulse'];
@endphp

<svg {{ $attributes->merge(['class' => 'h-4 w-4 shrink-0']) }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
    {!! $paths[$name] ?? $paths['cross'] !!}
</svg>
