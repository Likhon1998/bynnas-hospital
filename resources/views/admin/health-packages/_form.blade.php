<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.health-packages.index')"
    lede="Set the package price and what is included so reception can explain it and billing can charge it."
>
    <x-admin.form-section title="Package" note="Name and price shown to patients.">
        <x-form.input name="title" label="Package name" :value="$package->title" required />
        <x-form.input name="slug" label="Slug" :value="$package->slug" />
        <x-form.input name="price" label="Price" type="number" :value="$package->price" required />
        <x-form.input name="duration_label" label="Duration label" :value="$package->duration_label" />
        <div class="form-span-2">
            <x-form.textarea name="description" label="Description" :value="$package->description" />
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="Included items" note="Write one included item on each line." :columns="false">
        <x-form.textarea name="items" label="Items" :value="old('items', $itemsText)" :rows="8" />
    </x-admin.form-section>

    <x-admin.form-section title="Presentation">
        <x-form.input name="image" label="Image URL" :value="$package->image" />
        <x-form.input name="sort_order" label="Sort order" type="number" :value="$package->sort_order ?? 0" />
    </x-admin.form-section>

    <x-admin.form-section title="Visibility" :columns="false">
        <div class="form-grid">
            <x-form.checkbox name="is_featured" label="Featured package" :checked="$package->is_featured" />
            <x-form.checkbox name="is_active" label="Active" :checked="$package->is_active" />
        </div>
    </x-admin.form-section>
</x-admin.form-page>
