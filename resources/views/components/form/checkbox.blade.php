@props([
    'name',
    'label',
    'checked' => false,
])

<label class="flex items-center gap-2 text-sm text-navy-900">
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked)) class="rounded border-sand-100 text-teal-700">
    {{ $label }}
</label>
