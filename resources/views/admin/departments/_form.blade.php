<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.departments.index')"
    lede="Describe the department clearly so staff can assign doctors and the website can introduce the clinic."
>
    <x-admin.form-section title="Department" note="Name and description used across admin and the public site.">
        <x-form.input name="name" label="Name" :value="$department->name" required />
        <x-form.input name="slug" label="Slug" :value="$department->slug" />
        <div class="form-span-2">
            <x-form.textarea name="description" label="Description" :value="$department->description" />
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="Presentation" note="Optional image and icon for the public department page.">
        <x-form.input name="icon" label="Icon name" :value="$department->icon" />
        <x-form.input name="image" label="Image URL" :value="$department->image" />
        <x-form.input name="sort_order" label="Sort order" type="number" :value="$department->sort_order ?? 0" />
    </x-admin.form-section>

    <x-admin.form-section title="Visibility" :columns="false">
        <div class="form-grid">
            <x-form.checkbox name="is_featured" label="Featured on homepage" :checked="$department->is_featured" />
            <x-form.checkbox name="is_active" label="Active" :checked="$department->is_active" />
        </div>
    </x-admin.form-section>
</x-admin.form-page>
