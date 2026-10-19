@props([
    'name',
    'label',
    'type' => 'text',
    'value' => '',
    'required' => false,
])

<div>
    <label for="{{ $name }}" class="text-sm font-medium text-navy-900">{{ $label }}</label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'mt-2 w-full rounded-2xl border border-sand-100 bg-sand-50 px-4 py-3 text-sm outline-none ring-teal-700/20 focus:ring-2']) }}
    >
    @error($name) <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
</div>
