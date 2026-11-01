@props(['value' => ''])

@php
    $classes = match ($value) {
        'confirmed', 'active', 'published', 'paid', 'completed', 'given' => 'bg-emerald-50 text-emerald-800',
        'waiting', 'pending', 'issued', 'ordered', 'processing' => 'bg-amber-50 text-amber-800',
        'triage', 'in_progress', 'admitted', 'collected' => 'bg-sky-50 text-sky-800',
        'consultation', 'transferred' => 'bg-teal-50 text-teal-800',
        'critical' => 'bg-red-50 text-red-700',
        'discharge_authorized' => 'bg-amber-50 text-amber-800',
        'overdue', 'withheld', 'missed', 'draft', 'unpaid' => 'bg-red-50 text-red-700',
        'cancelled', 'no_show', 'inactive' => 'bg-red-50 text-red-700',
        default => 'bg-slate-100 text-slate-700',
    };

    $label = match ($value) {
        'no_show' => 'No show',
        'in_progress' => 'In progress',
        'discharge_authorized' => 'Discharge authorized',
        default => ucfirst(str_replace('_', ' ', (string) $value)),
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold '.$classes]) }}>
    {{ $label }}
</span>
