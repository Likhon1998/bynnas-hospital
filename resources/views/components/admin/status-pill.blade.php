@props(['value' => ''])

@php
    $classes = match ($value) {
        'confirmed', 'active', 'published', 'paid', 'completed' => 'bg-emerald-50 text-emerald-800',
        'pending', 'issued' => 'bg-amber-50 text-amber-800',
        'draft', 'unpaid' => 'bg-red-50 text-red-700',
        'cancelled', 'no_show', 'inactive' => 'bg-red-50 text-red-700',
        default => 'bg-slate-100 text-slate-700',
    };

    $label = match ($value) {
        'no_show' => 'No show',
        default => ucfirst(str_replace('_', ' ', (string) $value)),
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold '.$classes]) }}>
    {{ $label }}
</span>
