<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.testimonials.index')"
    lede="Record the patient voice exactly, then decide whether it should appear on the website."
>
    <x-admin.form-section title="Person" note="Who is speaking, and how they should be introduced.">
        <x-form.input name="display_name" label="Name" :value="$testimonial->display_name" required />
        <x-form.input name="display_role" label="Role / relation" :value="$testimonial->display_role" />
        <x-form.input name="photo" label="Photo URL" :value="$testimonial->photo" />
        <x-form.input name="rating" label="Rating (1-5)" type="number" :value="$testimonial->rating ?? 5" />
    </x-admin.form-section>

    <x-admin.form-section title="Quote" :columns="false">
        <x-form.textarea name="quote" label="Quote" :value="$testimonial->quote" required />
    </x-admin.form-section>

    <x-admin.form-section title="Visibility">
        <x-form.input name="sort_order" label="Sort order" type="number" :value="$testimonial->sort_order ?? 0" />
        <x-form.checkbox name="is_active" label="Approved and visible" :checked="$testimonial->is_active" />
    </x-admin.form-section>
</x-admin.form-page>
