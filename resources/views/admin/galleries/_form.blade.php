<form method="POST" action="{{ $action }}" class="max-w-3xl space-y-5 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <x-form.input name="title" label="Title" :value="$item->title" required />
    <x-form.input name="image" label="Image URL" :value="$item->image" required />
    <x-form.textarea name="caption" label="Caption" :value="$item->caption" />
    <x-form.input name="album" label="Album" :value="$item->album" />
    <x-form.input name="sort_order" label="Sort order" type="number" :value="$item->sort_order ?? 0" />
    <x-form.checkbox name="is_active" label="Visible" :checked="$item->is_active" />
    <div class="flex gap-3">
        <button type="submit" class="rounded-full bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save</button>
        <a href="{{ route('admin.galleries.index') }}" class="rounded-full px-5 py-3 text-sm font-semibold text-ink-600">Cancel</a>
    </div>
</form>
