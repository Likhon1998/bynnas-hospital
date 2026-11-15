<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.acknowledgements.index')"
    lede="These Bangla lines scroll in the site header ticker. Keep them short. They are separate from Notices."
>
    <x-admin.form-section title="Acknowledgement" :columns="false">
        <x-form.input
            name="message"
            label="Bangla message"
            :value="$acknowledgement->message"
            required
        />
    </x-admin.form-section>

    <x-admin.form-section title="Visibility">
        <x-form.input name="sort_order" label="Sort order" type="number" :value="$acknowledgement->sort_order ?? 0" />
        <x-form.checkbox name="is_active" label="Show in the header ticker" :checked="$acknowledgement->is_active" />
    </x-admin.form-section>
</x-admin.form-page>
