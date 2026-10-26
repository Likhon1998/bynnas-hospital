@props(['name' => 'dot'])

@php
    $paths = [
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 10.5 12 4l8 6.5V20a1 1 0 01-1 1h-5v-6H10v6H5a1 1 0 01-1-1v-9.5z"/>',
        'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 4v3m10-3v3M5 9h14M6 20h12a2 2 0 002-2V8a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a3 3 0 00-3-3H8a3 3 0 00-3 3v1m13-10a3 3 0 11-6 0 3 3 0 016 0zm6 10v-1a3 3 0 00-2-2.83"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 8a3 3 0 010 4"/>',
        'receipt' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 4h10a1 1 0 011 1v15l-2-1.4L14 20l-2-1.4L10 20l-2-1.4L6 20V5a1 1 0 011-1zm3 5h4M10 13h4"/>',
        'building' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 20V8l8-4 8 4v12M9 20v-6h6v6M9 10h.01M15 10h.01M9 14h.01M15 14h.01"/>',
        'stethoscope' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 4v5a4 4 0 008 0V4M8 4H6m10 0h2m-6 9v2a5 5 0 005 5h1a3 3 0 100-6"/>',
        'heart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-7-4.4-7-10a4 4 0 017-2 4 4 0 017 2c0 5.6-7 10-7 10z"/>',
        'package' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 8l8-4 8 4-8 4-8-4zm0 0v8l8 4 8-4V8M12 12v8"/>',
        'folder' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7a2 2 0 012-2h3.5l2 2H18a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2V7z"/>',
        'file' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 4h6l4 4v12a1 1 0 01-1 1H8a1 1 0 01-1-1V5a1 1 0 011-1zm6 0v4h4M9 13h6M9 17h4"/>',
        'quote' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 17c-1.5 0-3-1.2-3-3.2C4 10.5 6.2 8 9.5 7.2L10 9c-1.7.5-2.7 1.5-2.8 2.6.4-.3.9-.4 1.5-.4 1.3 0 2.3 1 2.3 2.4S9.3 17 7 17zm10 0c-1.5 0-3-1.2-3-3.2 0-3.3 2.2-5.8 5.5-6.6L20 9c-1.7.5-2.7 1.5-2.8 2.6.4-.3.9-.4 1.5-.4 1.3 0 2.3 1 2.3 2.4S19.3 17 17 17z"/>',
        'image' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 6h14a1 1 0 011 1v10a1 1 0 01-1 1H5a1 1 0 01-1-1V7a1 1 0 011-1zm0 9 3.5-3.5L12 15l2.5-2.5L19 16M9 9.5a1 1 0 110-2 1 1 0 010 2z"/>',
        'megaphone' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 11v2a3 3 0 003 3h1l1 3h2l-1-3h1l5 2V6l-5 2H8a3 3 0 00-3 3z"/>',
        'mail' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16v10H4V7zm0 0 8 6 8-6"/>',
        'settings' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 15.5a3.5 3.5 0 110-7 3.5 3.5 0 010 7zM19.4 13a7.8 7.8 0 00.1-2l2-1.2-2-3.4-2.3.6a8 8 0 00-1.7-1L15 3h-4l-.5 2.9a8 8 0 00-1.7 1L6.5 6.4l-2 3.4L6.4 11a7.8 7.8 0 000 2l-1.9 1.2 2 3.4 2.3-.6a8 8 0 001.7 1L11 21h4l.5-2.9a8 8 0 001.7-1l2.3.6 2-3.4L19.4 13z"/>',
        'shield' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 3v6c0 5-3.4 8.4-8 9-4.6-.6-8-4-8-9V6l8-3z"/>',
        'clipboard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6a2 2 0 012 2v12H7V7a2 2 0 012-2zm0 0V4h6v1M9 11h6M9 15h4"/>',
        'logout' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12H4m11 0-3-3m3 3-3 3m4-10h2a2 2 0 012 2v12a2 2 0 01-2 2h-2"/>',
        'search' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>',
        'globe' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zm0 0c2.5-2.2 4-5.4 4-9s-1.5-6.8-4-9c-2.5 2.2-4 5.4-4 9s1.5 6.8 4 9zM3.5 12h17"/>',
        'chevron' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>',
        'bell' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 11-6 0"/>',
        'clock' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'bed' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 18V9a2 2 0 012-2h5v7h9v4M4 18h16M8 7V5a1 1 0 011-1h2a1 1 0 011 1v2"/>',
        'spark' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3l1.2 4.2L17.5 8.5 13.2 9.8 12 14l-1.2-4.2L6.5 8.5l4.3-1.3L12 3zm6 8 .8 2.4L21 14.5l-2.2.7L18 17.5l-.8-2.3L15 14.5l2.2-.7L18 11z"/>',
        'badge' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 4h8l1 3H7l1-3zm-1 3h10v11a2 2 0 01-2 2H9a2 2 0 01-2-2V7zm3 4h4M10 14h4"/>',
        'grid' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 5h5v5H5V5zm9 0h5v5h-5V5zM5 14h5v5H5v-5zm9 0h5v5h-5v-5z"/>',
        'help' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zm0-3.2h.01M9.5 9.2A2.5 2.5 0 0112 7.5c1.4 0 2.5 1 2.5 2.4 0 1.4-1.1 2.1-2.5 2.6v1"/>',
        'dot' => '<circle cx="12" cy="12" r="3"/>',
    ];
@endphp

<svg {{ $attributes->merge(['class' => 'h-4 w-4 shrink-0']) }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
    {!! $paths[$name] ?? $paths['dot'] !!}
</svg>
