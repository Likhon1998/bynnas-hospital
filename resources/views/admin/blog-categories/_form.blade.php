<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.blog-categories.index')"
    lede="Group health articles so patients can browse them by topic."
>
    <x-admin.form-section title="Category">
        <x-form.input name="name" label="Name" :value="$category->name" required />
        <x-form.input name="slug" label="Slug" :value="$category->slug" />
        <x-form.input name="sort_order" label="Sort order" type="number" :value="$category->sort_order ?? 0" />
    </x-admin.form-section>

    <x-admin.form-section title="Visibility" :columns="false">
        <x-form.checkbox name="is_active" label="Active category" :checked="$category->is_active" />
    </x-admin.form-section>
</x-admin.form-page>
