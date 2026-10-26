@props([
    'name',
    'label',
    'required' => false,
])

<div>
    <label for="{{ $name }}" class="text-sm font-medium text-navy-900">{{ $label }}</label>
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'admin-field mt-2 w-full px-4 py-3 text-sm']) }}
    >
        {{ $slot }}
    </select>
    @error($name)
        <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
