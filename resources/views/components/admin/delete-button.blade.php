@props([
    'action',
    'label' => 'Delete',
    'variant' => 'link',
])

@php
    $buttonClass = $variant === 'pill'
        ? 'table-btn table-btn--danger'
        : 'text-sm font-semibold text-red-700 hover:text-red-800';
@endphp

<form method="POST" action="{{ $action }}" onsubmit="return confirm('Delete this record? This cannot be undone.')">
    @csrf
    @method('DELETE')
    <button type="submit" {{ $attributes->class([$buttonClass]) }}>{{ $label }}</button>
</form>
