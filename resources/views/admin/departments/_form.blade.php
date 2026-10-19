<form method="POST" action="{{ $action }}" class="max-w-3xl space-y-5 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif
    <x-form.input name="name" label="Name" :value="$department->name" required />
    <x-form.input name="slug" label="Slug" :value="$department->slug" />
    <x-form.textarea name="description" label="Description" :value="$department->description" />
    <x-form.input name="icon" label="Icon name" :value="$department->icon" />
    <x-form.input name="image" label="Image URL" :value="$department->image" />
    <x-form.input name="sort_order" label="Sort order" type="number" :value="$department->sort_order ?? 0" />
    <div class="flex flex-wrap gap-6">
        <x-form.checkbox name="is_featured" label="Featured on homepage" :checked="$department->is_featured" />
        <x-form.checkbox name="is_active" label="Active" :checked="$department->is_active" />
    </div>
    <div class="flex gap-3">
        <button type="submit" class="rounded-full bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save</button>
        <a href="{{ route('admin.departments.index') }}" class="rounded-full px-5 py-3 text-sm font-semibold text-ink-600">Cancel</a>
    </div>
</form>
