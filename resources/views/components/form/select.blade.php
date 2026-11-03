@props([
    'name',
    'label',
    'required' => false,
    'multiple' => false,
    'size' => null,
    'hint' => null,
    'disabled' => false,
])

@php
    $fieldId = rtrim($name, '[]');
    $fieldName = $multiple && ! str_ends_with($name, '[]') ? $name.'[]' : $name;
@endphp

<div @class(['form-span-2' => $multiple])>
    <label for="{{ $fieldId }}" class="text-sm font-medium text-navy-900">{{ $label }}</label>
    <select
        id="{{ $fieldId }}"
        name="{{ $fieldName }}"
        @if ($required) required @endif
        @if ($multiple) multiple @endif
        @if ($size) size="{{ $size }}" @endif
        @disabled($disabled)
        {{ $attributes->merge(['class' => 'admin-field mt-2 w-full px-4 py-3 text-sm'.($multiple ? ' admin-field--multiple' : '')]) }}
    >
        {{ $slot }}
    </select>
    @if ($hint)
        <p class="mt-1.5 text-xs text-ink-500">{{ $hint }}</p>
    @endif
    @error($fieldId)
        <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
