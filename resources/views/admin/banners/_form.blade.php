<form method="POST" action="{{ $action }}" class="max-w-3xl space-y-5 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <x-form.input name="title" label="Title" :value="$banner->title" required />
    <x-form.input name="subtitle" label="Subtitle" :value="$banner->subtitle" />
    <x-form.input name="image" label="Image URL" :value="$banner->image" required />
    <x-form.input name="cta_label" label="Button label" :value="$banner->cta_label" />
    <x-form.input name="cta_url" label="Button URL" :value="$banner->cta_url" />
    <x-form.input name="placement" label="Placement" :value="$banner->placement ?? 'hero'" required />
    <x-form.input name="starts_at" label="Starts at" type="datetime-local" :value="optional($banner->starts_at)->format('Y-m-d\TH:i')" />
    <x-form.input name="ends_at" label="Ends at" type="datetime-local" :value="optional($banner->ends_at)->format('Y-m-d\TH:i')" />
    <x-form.input name="sort_order" label="Sort order" type="number" :value="$banner->sort_order ?? 0" />
    <x-form.checkbox name="is_active" label="Active" :checked="$banner->is_active" />
    <div class="flex gap-3">
        <button type="submit" class="rounded-full bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save</button>
        <a href="{{ route('admin.banners.index') }}" class="rounded-full px-5 py-3 text-sm font-semibold text-ink-600">Cancel</a>
    </div>
</form>
