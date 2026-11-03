@props([
    'name',
    'label',
    'type' => 'text',
    'value' => '',
    'required' => false,
    'hint' => null,
])

<div>
    <label for="{{ $name }}" class="text-sm font-medium text-navy-900">{{ $label }}</label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'file')
            value="{{ old($name, $value) }}"
        @endif
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'admin-field mt-2 w-full px-4 py-3 text-sm']) }}
    >
    @if ($hint)
        <p class="mt-2 text-sm text-ink-600">{{ $hint }}</p>
    @endif
    @error($name) <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
</div>
