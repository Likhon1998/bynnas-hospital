@props(['value' => ''])

@php
    $classes = match ($value) {
        'confirmed', 'active', 'published', 'paid' => 'bg-teal-50 text-teal-800',
        'pending', 'issued' => 'bg-amber-50 text-amber-800',
        'completed' => 'bg-navy-900 text-white',
        'draft' => 'bg-sand-100 text-ink-600',
        'cancelled', 'no_show', 'inactive' => 'bg-red-50 text-red-800',
        default => 'bg-sand-100 text-ink-600',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold '.$classes]) }}>
    {{ str_replace('_', ' ', $value) }}
</span>
