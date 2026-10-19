<form method="POST" action="{{ $action }}" class="max-w-3xl space-y-5 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <x-form.input name="title" label="Package name" :value="$package->title" required />
    <x-form.input name="slug" label="Slug" :value="$package->slug" />
    <x-form.input name="price" label="Price" type="number" :value="$package->price" required />
    <x-form.input name="duration_label" label="Duration label" :value="$package->duration_label" />
    <x-form.textarea name="description" label="Description" :value="$package->description" />
    <x-form.input name="image" label="Image URL" :value="$package->image" />
    <x-form.textarea name="items" label="Included items (one per line)" :value="old('items', $itemsText)" :rows="8" />
    <x-form.input name="sort_order" label="Sort order" type="number" :value="$package->sort_order ?? 0" />
    <div class="flex flex-wrap gap-6">
        <x-form.checkbox name="is_featured" label="Featured" :checked="$package->is_featured" />
        <x-form.checkbox name="is_active" label="Active" :checked="$package->is_active" />
    </div>
    <div class="flex gap-3">
        <button type="submit" class="rounded-full bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save</button>
        <a href="{{ route('admin.health-packages.index') }}" class="rounded-full px-5 py-3 text-sm font-semibold text-ink-600">Cancel</a>
    </div>
</form>
