@props(['action', 'label' => 'Delete'])

<form method="POST" action="{{ $action }}" onsubmit="return confirm('Delete this record? This cannot be undone.')">
    @csrf
    @method('DELETE')
    <button type="submit" {{ $attributes->merge(['class' => 'text-sm font-semibold text-red-700 hover:text-red-800']) }}>{{ $label }}</button>
</form>
