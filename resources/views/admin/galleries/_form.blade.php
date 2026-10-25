<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.galleries.index')"
    lede="Add a gallery image with a clear title so visitors can understand what they are looking at."
>
    <x-admin.form-section title="Image">
        <x-form.input name="title" label="Title" :value="$item->title" required />
        <x-form.input name="album" label="Album" :value="$item->album" />
        <div class="form-span-2">
            <x-form.input name="image" label="Image URL" :value="$item->image" required />
        </div>
        <div class="form-span-2">
            <x-form.textarea name="caption" label="Caption" :value="$item->caption" />
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="Visibility">
        <x-form.input name="sort_order" label="Sort order" type="number" :value="$item->sort_order ?? 0" />
        <x-form.checkbox name="is_active" label="Visible in the gallery" :checked="$item->is_active" />
    </x-admin.form-section>
</x-admin.form-page>
