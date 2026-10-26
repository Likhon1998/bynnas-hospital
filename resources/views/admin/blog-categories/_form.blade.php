<form method="POST" action="{{ $action }}" class="max-w-3xl space-y-5 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <x-form.input name="name" label="Name" :value="$category->name" required />
    <x-form.input name="slug" label="Slug" :value="$category->slug" />
    <x-form.input name="sort_order" label="Sort order" type="number" :value="$category->sort_order ?? 0" />
    <x-form.checkbox name="is_active" label="Active" :checked="$category->is_active" />
    <div class="flex gap-3">
        <button type="submit" class="rounded-full bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save</button>
        <a href="{{ route('admin.blog-categories.index') }}" class="rounded-full px-5 py-3 text-sm font-semibold text-ink-600">Cancel</a>
    </div>
</form>
