@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'button',
])

@php
    $classes = match ($variant) {
        'secondary' => 'inline-flex items-center justify-center gap-2 rounded-full bg-white px-7 py-3 text-sm font-semibold text-teal-800 shadow-sm transition hover:bg-teal-50',
        'outline' => 'inline-flex items-center justify-center gap-2 rounded-full border border-white bg-transparent px-8 py-3 text-sm font-semibold text-white transition hover:bg-white/10',
        'outline-teal' => 'inline-flex items-center justify-center gap-2 rounded-full border border-teal-700 bg-white px-6 py-2.5 text-sm font-semibold text-teal-800 transition hover:bg-teal-50',
        'navy' => 'inline-flex items-center justify-center gap-2 rounded-full bg-teal-900 px-7 py-3 text-sm font-semibold text-white transition hover:bg-teal-800',
        'ghost' => 'inline-flex items-center justify-center gap-2 rounded-full px-4 py-2 text-sm font-semibold text-teal-800 transition hover:bg-teal-50',
        'emergency' => 'inline-flex items-center justify-center gap-2 rounded-full bg-white px-7 py-3 text-sm font-semibold text-teal-800 transition hover:bg-teal-50',
        default => 'inline-flex items-center justify-center gap-2 rounded-full bg-teal-700 px-7 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
