@props([
    'name',
    'label',
    'checked' => false,
])

<label class="form-toggle">
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked)) class="form-toggle__input">
    <span>{{ $label }}</span>
</label>
