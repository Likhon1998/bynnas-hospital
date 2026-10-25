<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.banners.index')"
    lede="Set the banner copy, image, and the window it should stay live."
>
    <x-admin.form-section title="Banner" note="What visitors see first.">
        <x-form.input name="title" label="Title" :value="$banner->title" required />
        <x-form.input name="subtitle" label="Subtitle" :value="$banner->subtitle" />
        <div class="form-span-2">
            <x-form.input name="image" label="Image URL" :value="$banner->image" required />
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="Button">
        <x-form.input name="cta_label" label="Button label" :value="$banner->cta_label" />
        <x-form.input name="cta_url" label="Button URL" :value="$banner->cta_url" />
    </x-admin.form-section>

    <x-admin.form-section title="Schedule" note="Placement and the dates this banner should run.">
        <x-form.input name="placement" label="Placement" :value="$banner->placement ?? 'hero'" required />
        <x-form.input name="sort_order" label="Sort order" type="number" :value="$banner->sort_order ?? 0" />
        <x-form.input name="starts_at" label="Starts at" type="datetime-local" :value="optional($banner->starts_at)->format('Y-m-d\TH:i')" />
        <x-form.input name="ends_at" label="Ends at" type="datetime-local" :value="optional($banner->ends_at)->format('Y-m-d\TH:i')" />
    </x-admin.form-section>

    <x-admin.form-section title="Visibility" :columns="false">
        <x-form.checkbox name="is_active" label="Active banner" :checked="$banner->is_active" />
    </x-admin.form-section>
</x-admin.form-page>
