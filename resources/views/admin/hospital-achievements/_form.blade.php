<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.hospital-achievements.index')"
    lede="Hospital milestones appear on the public About us page. Keep them short and factual."
>
    <x-admin.form-section title="Milestone" note="Year and title visitors will read on About us." tone="teal">
        <x-form.input name="year" label="Year / period" :value="$achievement->year" hint="Example: 2012, 2020–2022, or Ongoing" />
        <x-form.input name="title" label="Title" :value="$achievement->title" required />
        <div class="form-span-2">
            <x-form.textarea name="description" label="Description" :value="$achievement->description" rows="4" />
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="Visibility">
        <x-form.input name="sort_order" label="Sort order" type="number" :value="$achievement->sort_order ?? 0" />
        <x-form.checkbox name="is_active" label="Published on the website" :checked="$achievement->is_active" />
    </x-admin.form-section>
</x-admin.form-page>
