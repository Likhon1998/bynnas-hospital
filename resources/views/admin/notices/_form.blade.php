<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.notices.index')"
    lede="Notices appear on the public homepage above Departments. Keep them short and dated."
>
    <x-admin.form-section title="Notice" :columns="false">
        <x-form.input name="title" label="Title" :value="$notice->title" required />
        <x-form.textarea name="body" label="Details" :value="$notice->body" />
        <x-form.input
            name="published_on"
            label="Notice date"
            type="date"
            :value="old('published_on', optional($notice->published_on)->format('Y-m-d'))"
            required
        />
        <x-form.input
            name="expires_on"
            label="Expires on (optional)"
            type="date"
            :value="old('expires_on', optional($notice->expires_on)->format('Y-m-d'))"
        />
    </x-admin.form-section>

    <x-admin.form-section title="Visibility">
        <x-form.input name="sort_order" label="Sort order" type="number" :value="$notice->sort_order ?? 0" />
        <x-form.checkbox name="is_urgent" label="Mark as urgent" :checked="$notice->is_urgent" />
        <x-form.checkbox name="is_active" label="Published on the website" :checked="$notice->is_active" />
    </x-admin.form-section>
</x-admin.form-page>
